<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Supprimer les éventuels projets orphelins dont le porteur n'existe plus
        DB::table('projects')
            ->whereNotIn('entrepreneur_id', function ($query): void {
                $query->select('id')->from('entrepreneurs');
            })
            ->delete();

        // 2. Identifier et détacher les pivots sae_users (role = ENTREPRENEUR) sans profil Entrepreneur associé
        $orphanPivots = DB::table('sae_users')
            ->where('role', 'ENTREPRENEUR')
            ->whereNotExists(function ($query): void {
                $query->select(DB::raw(1))
                    ->from('entrepreneurs')
                    ->whereColumn('entrepreneurs.user_id', 'sae_users.user_id')
                    ->whereColumn('entrepreneurs.sae_id', 'sae_users.sae_id');
            })
            ->get(['id', 'user_id']);

        if ($orphanPivots->isEmpty()) {
            return;
        }

        $affectedUserIds = $orphanPivots->pluck('user_id')->filter()->unique()->values();

        DB::table('sae_users')
            ->whereIn('id', $orphanPivots->pluck('id')->all())
            ->delete();

        // 3. Supprimer les comptes utilisateurs orphelins (sans aucun autre rôle métier actif)
        foreach ($affectedUserIds as $userId) {
            $user = User::find($userId);

            if (! $user) {
                continue;
            }

            if ($user->isSuperAdmin() || $user->isSaeManager() || $user->isCoach()) {
                continue;
            }

            $hasEntrepreneurProfile = DB::table('entrepreneurs')
                ->where('user_id', $user->id)
                ->exists();

            if ($hasEntrepreneurProfile) {
                continue;
            }

            $hasAnyRemainingSaeRole = DB::table('sae_users')
                ->where('user_id', $user->id)
                ->exists();

            if ($hasAnyRemainingSaeRole) {
                continue;
            }

            $user->delete();
        }
    }

    public function down(): void
    {
        // Nettoyage de données orphelines non réversible.
    }
};
