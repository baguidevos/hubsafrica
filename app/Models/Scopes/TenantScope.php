<?php

namespace App\Models\Scopes;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // 1. Si nous sommes dans un Panel Filament avec Tenancy actif
        if (Filament::hasTenancy() && $tenant = Filament::getTenant()) {
            $builder->where($model->qualifyColumn('sae_id'), $tenant->getKey());

            return;
        }

        // 2. Si l'utilisateur est authentifié et possède une SAE active (ex: session ou API)
        if (auth()->check()) {
            $user = auth()->user();

            // Si Super Admin (PLATFORM_ADMIN), ne pas filtrer globalement sauf si une SAE spécifique est sélectionnée
            if (method_exists($user, ('hasRole')) && $user->hasRole('super_admin')) {
                return;
            }

            if (isset($user->active_sae_id) && $user->active_sae_id) {
                $builder->where($model->qualifyColumn('sae_id'), $user->active_sae_id);
            }
        }
    }
}
