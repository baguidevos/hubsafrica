<?php

namespace App\Models;

use App\Enums\IndicatorCategory;
use App\Enums\IndicatorStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicator extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'name',
        'definition',
        'formula',
        'source',
        'frequency',
        'responsible',
        'category',
        'unit',
        'target',
        'current',
        'previous',
        'status',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'status' => IndicatorStatus::class,
            'category' => IndicatorCategory::class,
            'target' => 'decimal:2',
            'current' => 'decimal:2',
            'previous' => 'decimal:2',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(IndicatorEntry::class, 'indicator_id')->orderByDesc('period_on');
    }
}
