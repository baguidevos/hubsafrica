<?php

namespace App\Models\Concerns;

use App\Models\Sae;
use App\Models\Scopes\TenantScope;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (! $model->sae_id) {
                if (Filament::hasTenancy() && $tenant = Filament::getTenant()) {
                    $model->sae_id = $tenant->getKey();
                } elseif (auth()->check() && isset(auth()->user()->active_sae_id)) {
                    $model->sae_id = auth()->user()->active_sae_id;
                }
            }
        });
    }

    public function sae(): BelongsTo
    {
        return $this->belongsTo(Sae::class, 'sae_id');
    }
}
