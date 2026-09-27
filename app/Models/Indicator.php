<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
            'status' => \App\Enums\IndicatorStatus::class,
            'category' => \App\Enums\IndicatorCategory::class,
            'target' => 'decimal:2',
            'current' => 'decimal:2',
            'previous' => 'decimal:2',
        ];
    }
}
