<?php

namespace App\Console\Commands;

use App\Enums\CoachStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\Gender;
use App\Enums\ProjectStatus;
use App\Models\Coach;
use App\Models\Entrepreneur;
use App\Models\Project;
use App\Models\Role;
use App\Models\Sae;
use App\Models\SaeType;
use App\Models\User;
use App\Services\SaeOnboardingService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportV1DatabaseCommand extends Command
{
    protected $signature = 'import:v1-database 
                            {--file=database_v1_full.json : Chemin vers le fichier JSON d\'export V1}
                            {--dry-run : Exécuter une simulation sans modifier la base de données}';

    protected $description = 'Importe et transforme l\'ensemble des SAEs, utilisateurs, coachs, entrepreneurs et projets de la V1 vers la V2';

    public function handle(SaeOnboardingService $onboardingService): int
    {
        $filePath = base_path($this->option('file'));
        $isDryRun = (bool) $this->option('dry-run');

        if (! file_exists($filePath)) {
            $this->error("Le fichier JSON n'existe pas : {$filePath}");

            return self::FAILURE;
        }

        $this->info("=== DÉMARRAGE DE L'IMPORTATION V1 -> V2 ===");
        if ($isDryRun) {
            $this->warn('MODE SIMULATION (Dry-Run) ACTIF : Aucune modification ne sera enregistrée.');
        }

        $rawContent = file_get_contents($filePath);
        $data = json_decode($rawContent, true);

        if (! is_array($data)) {
            $this->error('Erreur de décodage JSON : '.json_last_error_msg());

            return self::FAILURE;
        }

        DB::beginTransaction();

        try {
            // Initialisation des rôles globaux système
            setPermissionsTeamId(0);
            $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
            $responsableRole = Role::firstOrCreate(['name' => 'responsable', 'guard_name' => 'web']);
            $coachRole = Role::firstOrCreate(['name' => 'coach', 'guard_name' => 'web']);
            $entrepreneurRole = Role::firstOrCreate(['name' => 'entrepreneur', 'guard_name' => 'web']);

            // Mappings de correspondance d'IDs V1 -> V2
            $userMap = [];        // [v1_user_id => v2_user_model]
            $saeMap = [];         // [v1_sae_id => v2_sae_model]
            $typeSaeMap = [];     // [v1_type_id => v2_type_model]
            $entrepreneurMap = []; // [v1_entrepreneur_id => v2_entrepreneur_model]

            // -------------------------------------------------------------
            // 1. IMPORTATION DES TYPES DE SAE (type_saes -> sae_types)
            // -------------------------------------------------------------
            $this->newLine();
            $this->info('1. Importation des Types de SAE...');
            $v1Types = $data['type_saes'] ?? [];
            foreach ($v1Types as $oldType) {
                $name = trim($oldType['libelle']);
                $slug = Str::slug($name);

                $saeType = SaeType::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'description' => $oldType['description'] ?? null,
                        'color' => 'primary',
                        'icon' => 'heroicon-o-building-office-2',
                        'is_active' => true,
                    ]
                );

                $this->preserveTimestamps($saeType, $oldType['created_at'] ?? null, $oldType['updated_at'] ?? null);

                $typeSaeMap[$oldType['id']] = $saeType->id;
            }
            $this->info('   -> '.count($typeSaeMap).' types de SAE traités.');

            // -------------------------------------------------------------
            // 2. IMPORTATION DES ADMINISTRATEURS (admins -> users avec super_admin)
            // -------------------------------------------------------------
            $this->newLine();
            $this->info('2. Importation des Administrateurs Système...');
            $v1Admins = $data['admins'] ?? [];
            foreach ($v1Admins as $oldAdmin) {
                $email = strtolower(trim($oldAdmin['email']));
                $name = trim(($oldAdmin['nom'] ?? '').' '.($oldAdmin['prenom'] ?? ''));

                $adminUser = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name ?: 'Admin',
                        'phone' => trim(($oldAdmin['indicatif'] ?? '').' '.($oldAdmin['telephone'] ?? '')),
                        'password' => $oldAdmin['password'],
                        'status' => ($oldAdmin['is_active'] ?? 1) ? 'ACTIVE' : 'INACTIVE',
                    ]
                );

                $this->preserveTimestamps($adminUser, $oldAdmin['created_at'] ?? null, $oldAdmin['updated_at'] ?? null);

                setPermissionsTeamId(0);
                if (! $adminUser->hasRole('super_admin')) {
                    $adminUser->assignRole($superAdminRole);
                }

                $this->line("   [Admin] {$adminUser->name} ({$adminUser->email})");
            }

            // -------------------------------------------------------------
            // 3. IMPORTATION DES UTILISATEURS (users de la V1)
            // -------------------------------------------------------------
            $this->newLine();
            $this->info('3. Importation des Utilisateurs (Responsables & Coachs)...');
            $v1Users = $data['users'] ?? [];
            foreach ($v1Users as $oldUser) {
                $email = strtolower(trim($oldUser['email']));
                $name = trim(($oldUser['name'] ?? '').' '.($oldUser['prenom'] ?? ''));

                $phone = trim(($oldUser['indicatif'] ?? '').' '.($oldUser['telephone'] ?? ''));

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name ?: 'Utilisateur V1',
                        'phone' => $phone ?: null,
                        'password' => $oldUser['password'],
                        'status' => ($oldUser['is_active'] ?? 1) ? 'ACTIVE' : 'INACTIVE',
                    ]
                );

                $this->preserveTimestamps($user, $oldUser['created_at'] ?? null, $oldUser['updated_at'] ?? null);

                $userMap[$oldUser['id']] = $user;
            }
            $this->info('   -> '.count($userMap).' utilisateurs V1 importés.');

            // -------------------------------------------------------------
            // 4. IMPORTATION DES STRUCTURES SAE (saes)
            // -------------------------------------------------------------
            $this->newLine();
            $this->info('4. Importation des Structures SAE...');
            $v1Saes = $data['saes'] ?? [];

            // Déterminer le Responsable (owner_id) de chaque SAE
            // Dans la V1 : les users avec type_user_id == 2 (Responsable) liés à sae_id
            $v1SaeOwners = [];
            foreach ($v1Users as $u) {
                if (($u['type_user_id'] ?? null) == 2 && ! empty($u['sae_id'])) {
                    if (! isset($v1SaeOwners[$u['sae_id']])) {
                        $v1SaeOwners[$u['sae_id']] = $u['id'];
                    }
                }
            }

            foreach ($v1Saes as $oldSae) {
                $saeName = trim($oldSae['nom_sae']);
                $baseSlug = Str::slug($saeName);

                // Déterminer le responsable
                $v1OwnerId = $v1SaeOwners[$oldSae['id']] ?? null;
                $ownerUser = $v1OwnerId && isset($userMap[$v1OwnerId]) ? $userMap[$v1OwnerId] : null;

                $sae = Sae::firstOrCreate(
                    ['slug' => $baseSlug],
                    [
                        'name' => $saeName,
                        'owner_id' => $ownerUser?->id,
                        'email' => $oldSae['email_sae'] ?? null,
                        'logo' => $oldSae['logo'] ?? null,
                        'vision' => $oldSae['vision'] ?? null,
                        'mission' => $oldSae['mission'] ?? null,
                        'objectives' => $oldSae['objectifs'] ?? null,
                        'services' => $oldSae['services'] ?? null,
                        'description' => $oldSae['description'] ?? null,
                        'status' => 'ACTIVE',
                    ]
                );

                $this->preserveTimestamps($sae, $oldSae['created_at'] ?? null, $oldSae['updated_at'] ?? null);

                $saeMap[$oldSae['id']] = $sae;

                // Lier le responsable dans sae_users et assigner le rôle Spatie scoped
                if ($ownerUser) {
                    $ownerUser->saes()->syncWithoutDetaching([
                        $sae->id => [
                            'role' => 'RESPONSABLE',
                            'status' => 'ACTIVE',
                            'created_at' => $oldSae['created_at'] ?? now(),
                            'updated_at' => $oldSae['updated_at'] ?? now(),
                        ],
                    ]);

                    setPermissionsTeamId($sae->id);
                    if (! $ownerUser->hasRole('responsable')) {
                        $ownerUser->assignRole($responsableRole);
                    }

                    // Initialiser les packs et niveaux par défaut
                    if (! $isDryRun) {
                        $onboardingService->initializeSaeDefaults($sae, $ownerUser);
                    }
                }

                $this->line("   [SAE] #{$sae->id} {$sae->name} (Responsable: ".($ownerUser ? $ownerUser->name : 'Non assigné').')');
            }

            // -------------------------------------------------------------
            // 5. RATTACHEMENT DES TYPES DE SAE (sae_type_sae -> sae_sae_type)
            // -------------------------------------------------------------
            $this->newLine();
            $this->info('5. Liaison des types de SAE...');
            $v1SaeTypePivots = $data['sae_type_sae'] ?? [];
            $linkedTypesCount = 0;
            foreach ($v1SaeTypePivots as $pivot) {
                $v2Sae = $saeMap[$pivot['sae_id']] ?? null;
                $v2TypeId = $typeSaeMap[$pivot['type_sae_id']] ?? null;

                if ($v2Sae && $v2TypeId) {
                    $v2Sae->types()->syncWithoutDetaching([
                        $v2TypeId => [
                            'created_at' => $pivot['created_at'] ?? now(),
                            'updated_at' => $pivot['updated_at'] ?? now(),
                        ],
                    ]);
                    $linkedTypesCount++;
                }
            }
            $this->info("   -> {$linkedTypesCount} associations SAE-Types appliquées.");

            // -------------------------------------------------------------
            // 6. RATTACHEMENT DES MEMBRES & COACHS (sae_users & coaches)
            // -------------------------------------------------------------
            $this->newLine();
            $this->info('6. Configuration des profils Coachs et membres SAE...');
            foreach ($v1Users as $oldUser) {
                $user = $userMap[$oldUser['id']] ?? null;
                if (! $user) {
                    continue;
                }

                $typeUserId = $oldUser['type_user_id'] ?? null;
                $v1SaeId = $oldUser['sae_id'] ?? null;
                $sae = $v1SaeId ? ($saeMap[$v1SaeId] ?? null) : null;

                // Si c'est un Coach (type_user_id = 3)
                if ($typeUserId == 3 && $sae) {
                    // Lier dans le pivot sae_users
                    $user->saes()->syncWithoutDetaching([
                        $sae->id => [
                            'role' => 'COACH',
                            'status' => 'ACTIVE',
                            'created_at' => $oldUser['created_at'] ?? now(),
                            'updated_at' => $oldUser['updated_at'] ?? now(),
                        ],
                    ]);

                    // Assigner rôle Spatie avec Team
                    setPermissionsTeamId($sae->id);
                    if (! $user->hasRole('coach')) {
                        $user->assignRole($coachRole);
                    }

                    // Créer profil dans la table `coaches` si inexistant
                    $coach = Coach::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'sae_id' => $sae->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'status' => CoachStatus::ACTIVE,
                            'max_capacity' => 10,
                            'is_external' => false,
                        ]
                    );

                    $this->preserveTimestamps($coach, $oldUser['created_at'] ?? null, $oldUser['updated_at'] ?? null);

                    $this->line("   [Coach] {$user->name} rattaché à {$sae->name}");
                }
            }

            // Traiter également les liaisons de la table `sae_user` de la V1
            $v1SaeUsers = $data['sae_user'] ?? [];
            foreach ($v1SaeUsers as $su) {
                $user = $userMap[$su['user_id']] ?? null;
                $sae = $saeMap[$su['sae_id']] ?? null;

                if ($user && $sae) {
                    $roleName = ! empty($su['role']) ? strtoupper($su['role']) : 'COACH';
                    $user->saes()->syncWithoutDetaching([
                        $sae->id => [
                            'role' => $roleName,
                            'status' => 'ACTIVE',
                            'created_at' => $su['created_at'] ?? now(),
                            'updated_at' => $su['updated_at'] ?? now(),
                        ],
                    ]);

                    setPermissionsTeamId($sae->id);
                    if (strtoupper($roleName) === 'COACH') {
                        if (! $user->hasRole('coach')) {
                            $user->assignRole($coachRole);
                        }
                        $coach = Coach::firstOrCreate(
                            ['user_id' => $user->id],
                            [
                                'sae_id' => $sae->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'phone' => $user->phone,
                                'status' => CoachStatus::ACTIVE,
                                'max_capacity' => 10,
                                'is_external' => false,
                            ]
                        );

                        $this->preserveTimestamps($coach, $su['created_at'] ?? null, $su['updated_at'] ?? null);
                    }
                }
            }

            // -------------------------------------------------------------
            // 7. IMPORTATION DES ENTREPRENEURS (entrepreneurs -> users + entrepreneurs)
            // -------------------------------------------------------------
            $this->newLine();
            $this->info('7. Importation des Entrepreneurs et création de leurs comptes...');
            $v1Entrepreneurs = $data['entrepreneurs'] ?? [];
            $defaultSae = ! empty($saeMap) ? reset($saeMap) : null;

            foreach ($v1Entrepreneurs as $oldEnt) {
                $email = strtolower(trim($oldEnt['email']));
                if (empty($email)) {
                    continue;
                }

                $firstName = trim($oldEnt['prenom'] ?? '');
                $lastName = trim($oldEnt['nom'] ?? '');
                $fullName = trim("{$lastName} {$firstName}") ?: 'Entrepreneur V1';
                $phone = trim(($oldEnt['indicatif'] ?? '').' '.($oldEnt['telephone'] ?? ''));

                // 7.1 Créer ou récupérer le compte User pour l'authentification
                $entUser = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $fullName,
                        'phone' => $phone ?: null,
                        'password' => $oldEnt['password'],
                        'status' => 'ACTIVE',
                    ]
                );

                $this->preserveTimestamps($entUser, $oldEnt['created_at'] ?? null, $oldEnt['updated_at'] ?? null);

                // Déterminer la SAE de rattachement
                $v1SaeId = $oldEnt['sae_id'] ?? $oldEnt['requested_sae_id'] ?? null;
                $assignedSae = $v1SaeId && isset($saeMap[$v1SaeId]) ? $saeMap[$v1SaeId] : $defaultSae;

                if (! $assignedSae) {
                    $this->warn("   Aucune SAE disponible pour l'entrepreneur {$fullName}");

                    continue;
                }

                // 7.2 Normaliser le genre
                $rawGender = mb_strtolower(trim($oldEnt['genre'] ?? ''));
                $gender = match (true) {
                    str_contains($rawGender, 'f') => Gender::F,
                    str_contains($rawGender, 'm') => Gender::M,
                    default => null,
                };

                // 7.3 Créer le profil dans la table `entrepreneurs`
                $entrepreneur = Entrepreneur::firstOrCreate(
                    ['user_id' => $entUser->id],
                    [
                        'sae_id' => $assignedSae->id,
                        'first_name' => $firstName ?: $fullName,
                        'last_name' => $lastName ?: $fullName,
                        'email' => $email,
                        'phone' => $phone ?: null,
                        'gender' => $gender,
                        'country' => $oldEnt['pays_residence'] ?? $oldEnt['nationalite'] ?? 'Togo',
                        'territory' => $oldEnt['adresse'] ?? null,
                        'education_level' => $oldEnt['niveau_etude'] ?? null,
                        'profession' => $oldEnt['profession'] ?? null,
                        'professional_status' => $oldEnt['statut_professionnel'] ?? null,
                        'notes' => $oldEnt['motivation_projet'] ?? null,
                        'status' => EntrepreneurStatus::ACTIVE,
                    ]
                );

                $this->preserveTimestamps($entrepreneur, $oldEnt['created_at'] ?? null, $oldEnt['updated_at'] ?? null);

                $entrepreneurMap[$oldEnt['id']] = $entrepreneur;

                // 7.4 Rattacher au pivot sae_users et au rôle Spatie
                $entUser->saes()->syncWithoutDetaching([
                    $assignedSae->id => [
                        'role' => 'ENTREPRENEUR',
                        'status' => 'ACTIVE',
                        'created_at' => $oldEnt['created_at'] ?? now(),
                        'updated_at' => $oldEnt['updated_at'] ?? now(),
                    ],
                ]);

                setPermissionsTeamId($assignedSae->id);
                if (! $entUser->hasRole('entrepreneur')) {
                    $entUser->assignRole($entrepreneurRole);
                }
            }
            $this->info('   -> '.count($entrepreneurMap).' profils entrepreneurs importés et configurés.');

            // -------------------------------------------------------------
            // 8. IMPORTATION DES PROJETS (projets -> projects)
            // -------------------------------------------------------------
            $this->newLine();
            $this->info('8. Importation des Projets...');
            $v1Projets = $data['projets'] ?? [];
            $projectsCount = 0;

            foreach ($v1Projets as $oldProj) {
                $v1EntId = $oldProj['entrepreneur_id'] ?? null;
                $v2Ent = $v1EntId ? ($entrepreneurMap[$v1EntId] ?? null) : null;

                if (! $v2Ent) {
                    $this->warn("   Projet #{$oldProj['id']} ignoré : entrepreneur introuvable.");

                    continue;
                }

                // Parser les ODD (format JSON en V1 ex: "[1, 12]")
                $sdgs = null;
                if (! empty($oldProj['odd'])) {
                    $decoded = json_decode($oldProj['odd'], true);
                    $sdgs = is_array($decoded) ? $decoded : [$oldProj['odd']];
                }

                $project = Project::firstOrCreate(
                    [
                        'entrepreneur_id' => $v2Ent->id,
                        'name' => trim($oldProj['nom_du_projet']),
                    ],
                    [
                        'sae_id' => $v2Ent->sae_id,
                        'description' => $oldProj['resume_projet'] ?? null,
                        'problem' => $oldProj['problematique'] ?? null,
                        'product_service' => $oldProj['presentation_produit_service'] ?? null,
                        'value_proposition' => $oldProj['proposition_valeur'] ?? null,
                        'target' => ! empty($oldProj['segments_clients']) ? Str::limit(trim($oldProj['segments_clients']), 250) : null,
                        'sector' => ! empty($oldProj['domaine_projet']) ? Str::limit(trim($oldProj['domaine_projet']), 250) : null,
                        'sdgs' => $sdgs,
                        'logo' => $oldProj['logo'] ?? null,
                        'pitch' => $oldProj['pitch_projet'] ?? null,
                        'status' => ProjectStatus::ACTIF,
                    ]
                );

                $this->preserveTimestamps($project, $oldProj['created_at'] ?? null, $oldProj['updated_at'] ?? null);

                $projectsCount++;
                $this->line("   [Projet] {$oldProj['nom_du_projet']} (Entrepreneur: {$v2Ent->first_name} {$v2Ent->last_name})");
            }
            $this->info("   -> {$projectsCount} projets importés.");

            if ($isDryRun) {
                DB::rollBack();
                $this->newLine();
                $this->warn('=== SIMULATION TERMINÉE SANS ERREUR : La base de données reste intacte. ===');
            } else {
                DB::commit();
                $this->newLine();
                $this->info('=== IMPORTATION V1 -> V2 TERMINÉE AVEC SUCCÈS ! ===');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->newLine();
            $this->error("Erreur critique pendant l'importation : ".$e->getMessage());
            $this->error('Fichier : '.$e->getFile().' à la ligne '.$e->getLine());
            $this->line($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    /**
     * Conserve les timestamps exacts (created_at et updated_at) d'origine de la V1.
     */
    protected function preserveTimestamps(Model $model, mixed $createdAt, mixed $updatedAt): void
    {
        if (! $createdAt && ! $updatedAt) {
            return;
        }

        $model->timestamps = false;

        if ($createdAt) {
            $model->created_at = $createdAt;
        }

        if ($updatedAt) {
            $model->updated_at = $updatedAt;
        }

        $model->saveQuietly();
    }
}
