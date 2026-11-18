<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseLesson extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'course_module_id',
        'title',
        'slug',
        'description',
        'notes',
        'video_url',
        'video_path',
        'attachment_path',
        'duration_in_seconds',
        'is_free',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'is_published' => 'boolean',
            'duration_in_seconds' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $lesson): void {
            if (! $lesson->sae_id && $lesson->course_module_id) {
                $lesson->sae_id = CourseModule::withoutGlobalScopes()->whereKey($lesson->course_module_id)->value('sae_id');
            }

            if (blank($lesson->slug)) {
                $baseSlug = Str::slug($lesson->title) ?: 'lecon';
                $slug = $baseSlug;
                $counter = 2;

                while (static::withoutGlobalScopes()->where('sae_id', $lesson->sae_id)->where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }

                $lesson->slug = $slug;
            }
        });
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class, 'course_lesson_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isAccessibleBy(?Entrepreneur $entrepreneur): bool
    {
        if (! $this->is_published || ! $this->module?->is_published || ! $this->module?->course?->is_published) {
            return false;
        }

        if ($this->is_free || $this->module->is_free) {
            return true;
        }

        return $this->module->course->isAccessibleBy($entrepreneur);
    }

    public function getEmbedVideoUrl(): ?string
    {
        if (blank($this->video_url)) {
            return null;
        }

        $url = trim($this->video_url);

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]{6,})/', $url, $matches)) {
            return "https://www.youtube.com/embed/{$matches[1]}?rel=0";
        }

        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $url, $matches)) {
            return "https://player.vimeo.com/video/{$matches[1]}";
        }

        return $url;
    }

    public function getVideoFileUrl(): ?string
    {
        if (blank($this->video_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->video_path);
    }

    public function getAttachmentUrl(): ?string
    {
        if (blank($this->attachment_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }

    public function getFormattedDuration(): ?string
    {
        if (! $this->duration_in_seconds) {
            return null;
        }

        $minutes = intdiv($this->duration_in_seconds, 60);
        $seconds = $this->duration_in_seconds % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
