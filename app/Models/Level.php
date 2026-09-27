<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'name',
        'order',
        'color',
        'description',
        'min_average',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'min_average' => 'decimal:2',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'current_level_id');
    }

    public function deliverableDefinitions(): HasMany
    {
        return $this->hasMany(DeliverableDefinition::class, 'level_id');
    }
}
