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

    /**
     * @return array{key: string, label: string, badgeClasses: string}
     */
    public function fileTypeBadge(): array
    {
        return self::resolveFileTypeBadge(
            format: $this->format,
            filename: $this->original_filename ?: $this->file_path,
            driveUrl: $this->drive_url,
        );
    }

    /**
     * Détermine le type visuel d'un outil (icône, libellé et couleurs) sans dépendance externe.
     *
     * @return array{key: string, label: string, badgeClasses: string}
     */
    public static function resolveFileTypeBadge(
        ?AccompanimentToolFormat $format,
        ?string $filename = null,
        ?string $driveUrl = null,
    ): array {
        if ($format === AccompanimentToolFormat::DRIVE_LINK) {
            $normalizedUrl = strtolower((string) $driveUrl);
            $isGoogleDrive = str_contains($normalizedUrl, 'drive.google.com')
                || str_contains($normalizedUrl, 'docs.google.com');

            if ($isGoogleDrive) {
                return [
                    'key' => 'google_drive',
                    'label' => 'DRIVE',
                    'badgeClasses' => 'bg-amber-50 text-amber-800 border-amber-200',
                ];
            }

            return [
                'key' => 'link',
                'label' => 'LIEN',
                'badgeClasses' => 'bg-violet-50 text-violet-700 border-violet-200',
            ];
        }

        $extension = strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => [
                'key' => 'pdf',
                'label' => 'PDF',
                'badgeClasses' => 'bg-rose-50 text-rose-700 border-rose-200',
            ],
            'doc', 'docx', 'odt', 'rtf' => [
                'key' => 'word',
                'label' => strtoupper($extension),
                'badgeClasses' => 'bg-blue-50 text-blue-700 border-blue-200',
            ],
            'xls', 'xlsx', 'csv', 'ods' => [
                'key' => 'excel',
                'label' => strtoupper($extension),
                'badgeClasses' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            'ppt', 'pptx', 'odp' => [
                'key' => 'powerpoint',
                'label' => strtoupper($extension),
                'badgeClasses' => 'bg-orange-50 text-orange-700 border-orange-200',
            ],
            'zip', 'rar', '7z' => [
                'key' => 'archive',
                'label' => strtoupper($extension),
                'badgeClasses' => 'bg-slate-100 text-slate-700 border-slate-200',
            ],
            default => [
                'key' => 'document',
                'label' => $extension !== '' ? strtoupper($extension) : 'DOC',
                'badgeClasses' => 'bg-slate-100 text-slate-700 border-slate-200',
            ],
        };
    }
}
