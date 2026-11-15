<?php

namespace App\Models;

use App\Enums\AccompanimentToolRequestStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccompanimentToolAccessRequest extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'accompaniment_tool_id',
        'sae_id',
        'user_id',
        'status',
        'request_message',
        'decision_note',
        'reviewed_by_id',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AccompanimentToolRequestStatus::class,
            'reviewed_at' => 'datetime',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }
}
