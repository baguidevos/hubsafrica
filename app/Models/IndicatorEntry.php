<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorEntry extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'indicator_id',
        'project_id',
        'enrollment_id',
        'period_on',
        'value',
        'comment',
        'reported_by_id',
        'validated_by_id',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_on' => 'date',
            'value' => 'decimal:2',
            'validated_at' => 'datetime',
        ];
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class, 'indicator_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_id');
    }
}
