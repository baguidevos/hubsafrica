<?php

namespace App\Models;

use App\Enums\DocumentCategory;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'sae_id',
        'documentable_type',
        'documentable_id',
        'category',
        'title',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'version',
        'uploaded_by_id',
    ];

    protected function casts(): array
    {
        return [
            'category' => DocumentCategory::class,
            'version' => 'integer',
            'size' => 'integer',
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk($this->disk)->url($this->path) : null;
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size ?? 0;
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'Ko', 'Mo', 'Go', 'To'];
        $power = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $power), 2).' '.($units[$power] ?? 'B');
    }
}
