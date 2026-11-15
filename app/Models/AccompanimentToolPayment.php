<?php

namespace App\Models;

use App\Enums\AccompanimentToolPaymentStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccompanimentToolPayment extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'accompaniment_tool_id',
        'sae_id',
        'user_id',
        'reference',
        'external_reference',
        'gateway',
        'amount',
        'currency',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => AccompanimentToolPaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(AccompanimentTool::class, 'accompaniment_tool_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
