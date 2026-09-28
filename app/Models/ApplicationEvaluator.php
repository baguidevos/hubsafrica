<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationEvaluator extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'cohort_id',
        'application_id',
        'user_id',
        'role',
    ];

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class, 'cohort_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
