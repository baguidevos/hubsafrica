<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CourseModule extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'course_id',
        'title',
        'slug',
        'description',
        'is_free',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $module): void {
            if (! $module->sae_id && $module->course_id) {
                $module->sae_id = Course::withoutGlobalScopes()->whereKey($module->course_id)->value('sae_id');
            }

            if (blank($module->slug)) {
                $baseSlug = Str::slug($module->title) ?: 'module';
                $slug = $baseSlug;
                $counter = 2;

                while (static::withoutGlobalScopes()->where('sae_id', $module->sae_id)->where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }

                $module->slug = $slug;
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class, 'course_module_id')->orderBy('sort_order');
    }
}
