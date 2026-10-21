<?php

namespace App\Console\Commands;

use App\Enums\CohortStatus;
use App\Enums\ProgramStatus;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ImportCubeProjectsCommand extends Command
{
    protected $signature = 'sae:import-cube-projects
                            {--file=projets_incubation_cube.csv : Chemin relatif vers le fichier CSV}
                            {--sae=cube : Slug ou ID de la structure SAE cible}
                            {--program=Projet D-CLIC (OIF) : Nom du programme à créer ou associer}
                            {--cohort=Promotion Incubation D-CLIC : Nom de la cohorte à créer ou associer}
                            {--default-password=Cube@2026! : Mot de passe par défaut pour les comptes utilisateurs}
                            {--dry-run : Exécuter une simulation sans modifier la base de données}';

    protected $description = 'Importe les projets, entrepreneurs et cohortes du fichier CSV pour une SAE spécifique (ex: CUBE)';

    public function handle(): int
    {
        $filePath = base_path($this->option('file'));
        $saeIdentifier = $this->option('sae');
        $programName = $this->option('program');
        $cohortName = $this->option('cohort');
        $defaultPassword = $this->option('default-password');
        $isDryRun = (bool) $this->option('dry-run');

        $this->info('=======================================================');
        $this->info(" IMPORTATION DES PROJETS D'INCUBATION DANS LA SAE ");
        $this->info('=======================================================');

        if (! file_exists($filePath)) {
            $this->error("Fichier CSV introuvable à l'emplacement : {$filePath}");

            return self::FAILURE;
        }

        // 1. Recherche de la SAE cible
        $sae = Sae::where('slug', $saeIdentifier)
            ->orWhere('id', is_numeric($saeIdentifier) ? (int) $saeIdentifier : 0)
            ->first();

        if (! $sae) {
            $this->error("Aucune SAE trouvée avec l'identifiant ou le slug : '{$saeIdentifier}'");

            return self::FAILURE;
        }

        $this->info("Structure cible : {$sae->name} (ID: {$sae->id}, Slug: {$sae->slug})");

        if ($isDryRun) {
            $this->warn('MODE SIMULATION (DRY-RUN) : Aucune écriture en base de données ne sera effectuée.');
        }

        // 2. Lecture et parsing du CSV
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $this->error("Impossible d'ouvrir le fichier CSV.");

            return self::FAILURE;
        }

        // Lecture de l'en-tête BOM éventuel et de la ligne d'en-tête
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = fgetcsv($handle, 0, ',');
        if (! $headers) {
            $this->error("L'en-tête du fichier CSV est vide ou illisible.");
            fclose($handle);

            return self::FAILURE;
        }

        // Nettoyage des noms de colonnes
        $headers = array_map(function ($h) {
            return trim(str_replace(["\xEF\xBB\xBF", '"'], '', $h));
        }, $headers);

        $rows = [];
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if (count($data) >= count($headers)) {
                $rows[] = array_combine(array_slice($headers, 0, count($data)), $data);
            }
        }
        fclose($handle);

        $totalRows = count($rows);
        $this->info("Nombre de startups à traiter dans le CSV : {$totalRows}");

        if ($totalRows === 0) {
            $this->warn('Aucune ligne de données détectée dans le CSV.');

            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->table(['N°', 'Nom du Projet', 'Secteur', 'Porteur', 'Email', 'Téléphone'], array_map(function ($r) {
                return [
                    $r['N°'] ?? '—',
                    $r['Nom du Projet'] ?? '—',
                    $r['Secteur'] ?? '—',
                    $r['Porteur de Projet'] ?? '—',
                    $r['Email'] ?? '—',
                    $r['Téléphone'] ?? '—',
                ];
            }, $rows));

            $this->info('Simulation terminée avec succès. 0 modification enregistrée.');

            return self::SUCCESS;
        }

        // 3. Exécution réelle avec transaction
        DB::beginTransaction();

        try {
            // A. Création ou récupération du Programme
            $program = Program::firstOrCreate(
                [
                    'sae_id' => $sae->id,
                    'name' => $programName,
                ],
                [
                    'description' => "Programme d'accompagnement et d'incubation porté par {$sae->name} en synergie avec les partenaires techniques et financiers.",
                    'status' => ProgramStatus::ACTIF,
                ]
            );

            // B. Création ou récupération de la Cohorte
            $cohort = Cohort::firstOrCreate(
                [
                    'sae_id' => $sae->id,
                    'program_id' => $program->id,
                    'name' => $cohortName,
                ],
                [
                    'capacity' => max(30, $totalRows + 10),
                    'status' => CohortStatus::EN_COURS,
                    'start_date' => now()->startOfYear(),
                    'end_date' => now()->addYear(),
                    'objectives' => "Accompagnement intensif, structuration, levée de fonds et mise à l'échelle des startups sélectionnées.",
                ]
            );

            $this->info("Programme : {$program->name} (ID: {$program->id})");
            $this->info("Cohorte active : {$cohort->name} (ID: {$cohort->id})");

            $importedCount = 0;
            $bar = $this->output->createProgressBar($totalRows);
            $bar->start();

            foreach ($rows as $row) {
                $rawEmail = trim($row['Email'] ?? '');
                $rawFullName = trim($row['Porteur de Projet'] ?? '');
                $rawPhone = trim($row['Téléphone'] ?? '');
                $rawProjectName = trim($row['Nom du Projet'] ?? '');
                $rawSector = trim($row['Secteur'] ?? 'Généraliste');
                $rawDescription = trim($row['Description'] ?? '');
                $rawWebsite = trim($row['Site Web'] ?? '');
                $rawExecSummary = trim($row['Résumé Exécutif (Lien)'] ?? '');
                $rawPitchDeck = trim($row['Pitch Deck (Lien)'] ?? '');
                $rawScreenshot = trim($row["Capture d'écran"] ?? '');
                $rawPartner = trim($row['Partenaires / Programme'] ?? '');

                if (empty($rawEmail) || empty($rawProjectName)) {
                    $bar->advance();

                    continue;
                }

                // Découpage du prénom et nom
                $nameParts = preg_split('/\s+/', $rawFullName);
                $firstName = array_shift($nameParts) ?? 'Porteur';
                $lastName = implode(' ', $nameParts) ?: $firstName;

                // 1) Création ou mise à jour de l'utilisateur
                $user = User::where('email', $rawEmail)->first();
                if (! $user) {
                    $user = User::create([
                        'name' => $rawFullName ?: "{$firstName} {$lastName}",
                        'email' => $rawEmail,
                        'password' => Hash::make($defaultPassword),
                        'phone' => $rawPhone ?: null,
                        'status' => 'ACTIVE',
                        'email_verified_at' => now(),
                    ]);
                } else {
                    $user->update([
                        'name' => $rawFullName ?: $user->name,
                        'phone' => $rawPhone ?: $user->phone,
                    ]);
                }

                // Rattachement dans sae_users
                $user->saes()->syncWithoutDetaching([
                    $sae->id => [
                        'role' => 'ENTREPRENEUR',
                        'status' => 'ACTIVE',
                    ],
                ]);

                // Rôle Spatie dans le tenant
                setPermissionsTeamId($sae->id);
                $entrepreneurRole = Role::firstOrCreate(['name' => 'entrepreneur', 'guard_name' => 'web']);
                $user->assignRole($entrepreneurRole);

                // 2) Création ou mise à jour de la fiche Entrepreneur
                $entrepreneur = Entrepreneur::updateOrCreate(
                    [
                        'sae_id' => $sae->id,
                        'email' => $rawEmail,
                    ],
                    [
                        'user_id' => $user->id,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $rawPhone ?: null,
                        'country' => 'Togo',
                        'territory' => 'Lomé / Maritime',
                        'photo' => $rawScreenshot ?: null,
                        'status' => 'ACTIVE',
                    ]
                );

                // 3) Préparation des liens et documents dans la fiche Projet
                $pitchLinks = [];
                if (! empty($rawWebsite)) {
                    $pitchLinks[] = "Site web officiel : {$rawWebsite}";
                }
                if (! empty($rawExecSummary)) {
                    $pitchLinks[] = "Résumé Exécutif : {$rawExecSummary}";
                }
                if (! empty($rawPitchDeck)) {
                    $pitchLinks[] = "Pitch Deck : {$rawPitchDeck}";
                }
                if (! empty($rawPartner)) {
                    $pitchLinks[] = "Cadre / Partenaire : {$rawPartner}";
                }

                $project = Project::updateOrCreate(
                    [
                        'sae_id' => $sae->id,
                        'entrepreneur_id' => $entrepreneur->id,
                        'name' => $rawProjectName,
                    ],
                    [
                        'sector' => $rawSector,
                        'description' => $rawDescription,
                        'pitch' => ! empty($pitchLinks) ? implode("\n", $pitchLinks) : null,
                        'logo' => $rawScreenshot ?: null,
                        'status' => 'ACTIF',
                    ]
                );

                // 4) Inscription automatique dans la Cohorte active
                Enrollment::updateOrCreate(
                    [
                        'sae_id' => $sae->id,
                        'cohort_id' => $cohort->id,
                        'entrepreneur_id' => $entrepreneur->id,
                    ],
                    [
                        'project_id' => $project->id,
                        'status' => 'ACTIF',
                        'enrolled_at' => now(),
                    ]
                );

                $importedCount++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            DB::commit();

            $this->info("SUCCÈS : {$importedCount} startups, porteurs et projets ont été importés et inscrits dans la cohorte '{$cohort->name}' de la structure '{$sae->name}' !");
            $this->info("Mot de passe par défaut configuré pour les utilisateurs : {$defaultPassword}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->newLine();
            $this->error("Erreur durant l'importation : ".$e->getMessage());
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }
}
