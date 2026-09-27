<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingRecord extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'project_id',
        'amount_sought',
        'amount_obtained',
        'amount_invested',
        'source',
        'type',
        'status',
        'date',
        'justification',
        'verified',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\FundingStatus::class,
            'type' => \App\Enums\FundingType::class,
            'amount_sought' => 'decimal:2',
            'amount_obtained' => 'decimal:2',
            'amount_invested' => 'decimal:2',
            'date' => 'date',
            'verified' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
