<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Données de base indispensables pour démarrer un environnement de production vierge.
     * Ce seeder est 100% idempotent et ne contient AUCUNE fausse donnée de démonstration
     * (ni faux projets, ni faux mentors, ni faux financements, ni fausses SAEs).
     */
    public function run(): void
    {
        // 1. Référentiel des types de SAE (10 typologies institutionnelles)
        $this->call(SaeTypeSeeder::class);

        // 2. Rôles système fondamentaux (Spatie avec sae_id = 0 pour le scope global)
        setPermissionsTeamId(0);
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'responsable', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'coach', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'entrepreneur', 'guard_name' => 'web']);

        // 3. Super Administrateur initial (paramétrable via .env)
        $adminEmail = env('INITIAL_ADMIN_EMAIL', 'admin@hubafrica.org');
        $adminPassword = env('INITIAL_ADMIN_PASSWORD', 'AdminSecureHub2026!');
        $adminName = env('INITIAL_ADMIN_NAME', 'Super Administrateur Hub Africa');

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'phone' => env('INITIAL_ADMIN_PHONE', null),
                'status' => 'ACTIVE',
            ]
        );

        if (! $admin->hasRole('super_admin')) {
            $admin->assignRole($superAdminRole);
        }

        $this->command?->info('✓ Socle de production initialisé avec succès :');
        $this->command?->info('  • 10 Types officiels de SAE créés (SaeTypeSeeder)');
        $this->command?->info('  • 4 Rôles applicatifs configurés (super_admin, responsable, coach, entrepreneur)');
        $this->command?->info("  • Compte Super Administrateur : {$adminEmail}");
        $this->command?->warn('  ⚠️ N\'oubliez pas de modifier le mot de passe administrateur dès votre première connexion.');
    }
}
