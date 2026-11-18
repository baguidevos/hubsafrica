<?php

namespace App\Models;

use App\Enums\AccompanimentToolAccessLevel;
use App\Enums\AccompanimentToolFormat;
use App\Enums\AccompanimentToolPaymentStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Support\AccompanimentToolAccessDecision;
use App\Support\AccompanimentToolAccessPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AccompanimentTool extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'created_by_id',
        'title',
        'slug',
        'description',
        'category',
        'format',
        'file_path',
        'original_filename',
        'drive_url',
        'access_level',
        'price',
        'currency',
        'is_published',
        'is_shared_with_network',
    ];

    protected function casts(): array
    {
        return [
            'format' => AccompanimentToolFormat::class,
            'access_level' => AccompanimentToolAccessLevel::class,
            'price' => 'integer',
            'is_published' => 'boolean',
            'is_shared_with_network' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $tool): void {
            if (blank($tool->slug)) {
                $baseSlug = Str::slug($tool->title) ?: 'outil';
                $tool->slug = $baseSlug.'-'.Str::lower(Str::random(6));
            }
        });
    }

    public function sae(): BelongsTo
    {
        return $this->belongsTo(Sae::class, 'sae_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function accessRequests(): HasMany
    {
        return $this->hasMany(AccompanimentToolAccessRequest::class, 'accompaniment_tool_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AccompanimentToolPayment::class, 'accompaniment_tool_id');
    }

    public function saeTypes(): BelongsToMany
    {
        return $this->belongsToMany(SaeType::class, 'accompaniment_tool_sae_type', 'accompaniment_tool_id', 'sae_type_id')
            ->withTimestamps();
    }

    /**
     * Filtre les outils partagés sur le réseau correspondant aux types d'une SAE (ou universels).
     */
    public function scopeSharedForSae(Builder $query, ?Sae $viewerSae, ?int $selectedTypeId = null): Builder
    {
        $query->withoutGlobalScopes()
            ->where('is_published', true)
            ->where('is_shared_with_network', true);

        if ($selectedTypeId !== null) {
            return $query->where(function (Builder $subQuery) use ($selectedTypeId): void {
                $subQuery->whereDoesntHave('saeTypes')
                    ->orWhereHas('saeTypes', fn (Builder $typeQuery) => $typeQuery->where('sae_types.id', $selectedTypeId));
            });
        }

        if (! $viewerSae) {
            return $query->whereDoesntHave('saeTypes');
        }

        $viewerTypeIds = $viewerSae->types()->pluck('sae_types.id')->all();

        if ($viewerTypeIds === []) {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($viewerTypeIds): void {
            $subQuery->whereDoesntHave('saeTypes')
                ->orWhereHas('saeTypes', fn (Builder $typeQuery) => $typeQuery->whereIn('sae_types.id', $viewerTypeIds));
        });
    }

    public function evaluateAccessFor(?User $user): AccompanimentToolAccessDecision
    {
        if (! $user) {
            return AccompanimentToolAccessPolicy::evaluate(
                accessLevel: $this->access_level,
                isPublished: (bool) $this->is_published,
                isAuthenticated: false,
            );
        }

        $accessRequest = $this->accessRequests()
            ->withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->first();

        $hasPaidAccess = $this->payments()
            ->withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('status', AccompanimentToolPaymentStatus::PAID->value)
            ->exists();

        return AccompanimentToolAccessPolicy::evaluate(
            accessLevel: $this->access_level,
            isPublished: (bool) $this->is_published,
            isAuthenticated: true,
            requestStatus: $accessRequest?->status,
            hasPaidAccess: $hasPaidAccess,
        );
    }

    public function formattedPrice(): string
    {
        $amount = number_format((int) ($this->price ?? 0), 0, ',', ' ');

        return "{$amount} {$this->currency}";
    }
}
