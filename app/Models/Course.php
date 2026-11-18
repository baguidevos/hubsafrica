<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Course extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'level_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $course): void {
            if (blank($course->slug)) {
                $baseSlug = Str::slug($course->title) ?: 'cours';
                $slug = $baseSlug;
                $counter = 2;

                while (static::withoutGlobalScopes()->where('sae_id', $course->sae_id)->where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }

                $course->slug = $slug;
            }
        });
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class, 'course_id')->orderBy('sort_order');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(CourseLesson::class, CourseModule::class, 'course_id', 'course_module_id');
    }

    public function isAccessibleBy(?Entrepreneur $entrepreneur): bool
    {
        if (! $this->is_published) {
            return false;
        }

        if ($this->level_id === null) {
            return true;
        }

        if (! $entrepreneur) {
            return false;
        }

        return Project::withoutGlobalScopes()
            ->where('entrepreneur_id', $entrepreneur->id)
            ->where('current_level_id', $this->level_id)
            ->exists();
    }
}
