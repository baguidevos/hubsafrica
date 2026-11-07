<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Opportunity extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'sae_id',
        'title',
        'slug',
        'image',
        'sectors',
        'target_zones',
        'details',
        'learn_more_url',
        'apply_url',
        'deadline',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sectors' => 'array',
            'target_zones' => 'array',
            'deadline' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $opportunity): void {
            if (blank($opportunity->slug)) {
                $baseSlug = Str::slug($opportunity->title);
                $uniqueSuffix = Str::lower(Str::random(6));
                $opportunity->slug = "{$baseSlug}-{$uniqueSuffix}";
            }
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return Storage::disk('public')->url($this->image);
    }

    /**
     * URL publique et canonique de partage direct.
     * Totalement agnostique et opaque quant à la structure de la base de données.
     */
    public function getShareUrlAttribute(): string
    {
        return route('public.opportunities.detail', ['slug' => $this->slug]);
    }

    /**
     * Liens pré-formatés pour le partage sur les réseaux sociaux.
     *
     * @return array<string, string>
     */
    public function getShareLinksAttribute(): array
    {
        $shareUrl = $this->share_url;
        $encodedUrl = rawurlencode($shareUrl);
        $encodedText = rawurlencode("Découvrez cette opportunité sur Hubs Africa : {$this->title}");

        return [
            'copy' => $shareUrl,
            'whatsapp' => "https://api.whatsapp.com/send?text={$encodedText}%20{$encodedUrl}",
            'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$encodedUrl}",
            'twitter' => "https://twitter.com/intent/tweet?url={$encodedUrl}&text={$encodedText}",
            'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}",
            'email' => 'mailto:?subject='.rawurlencode("Opportunité : {$this->title}")."&body={$encodedText}%0A%0A{$encodedUrl}",
        ];
    }

    /**
     * Détermine si l'opportunité est clôturée.
     * Une opportunité est clôturée si elle est désactivée OU si sa date limite est échue.
     */
    public function isClosed(): bool
    {
        if (! $this->is_active) {
            return true;
        }

        if ($this->deadline instanceof \DateTimeInterface && now()->greaterThan($this->deadline)) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'opportunité est actuellement ouverte aux candidatures.
     */
    public function isOpen(): bool
    {
        return ! $this->isClosed();
    }

    /**
     * Calcule le nombre de jours calendaires restants avant clôture.
     * Retourne null si aucune date limite n'est définie.
     */
    public function daysRemaining(): ?int
    {
        if (! $this->deadline instanceof \DateTimeInterface) {
            return null;
        }

        if ($this->isClosed()) {
            return 0;
        }

        return (int) now()->diffInDays($this->deadline, false);
    }

    /**
     * Libellé explicatif pour la date limite et le statut d'échéance.
     */
    public function getDeadlineStatusLabel(): string
    {
        if ($this->isClosed()) {
            $formattedDate = $this->deadline ? Carbon::parse($this->deadline)->translatedFormat('d F Y à H:i') : null;

            return $formattedDate ? "Clôturé le {$formattedDate}" : 'Clôturé';
        }

        if (! $this->deadline instanceof \DateTimeInterface) {
            return 'Candidatures ouvertes (Date limite non précisée)';
        }

        $remainingDays = $this->daysRemaining();
        $carbonDeadline = Carbon::parse($this->deadline);

        if ($remainingDays === 0) {
            return "Clôture aujourd'hui (".$carbonDeadline->translatedFormat('d F Y à H:i').')';
        }

        if ($remainingDays === 1) {
            return 'Dernier jour restant (Clôture le '.$carbonDeadline->translatedFormat('d F Y').')';
        }

        return 'Clôture le '.$carbonDeadline->translatedFormat('d F Y')." (J-{$remainingDays})";
    }

    /**
     * Scope pour les opportunités publiées et visibles sur le front.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les opportunités encore ouvertes.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $subQuery): void {
                $subQuery->whereNull('deadline')
                    ->orWhere('deadline', '>=', now());
            });
    }

    /**
     * Scope pour les opportunités clôturées (soit date dépassée, soit désactivées).
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where(function (Builder $subQuery): void {
            $subQuery->where('is_active', false)
                ->orWhere(function (Builder $nestedQuery): void {
                    $nestedQuery->whereNotNull('deadline')
                        ->where('deadline', '<', now());
                });
        });
    }

    /**
     * Scope de recherche textuelle dans le titre, les détails, les secteurs et les zones.
     */
    public function scopeSearch(Builder $query, ?string $searchTerm): Builder
    {
        if (blank($searchTerm)) {
            return $query;
        }

        $cleanedTerm = trim($searchTerm);

        return $query->where(function (Builder $subQuery) use ($cleanedTerm): void {
            $subQuery->where('title', 'like', "%{$cleanedTerm}%")
                ->orWhere('details', 'like', "%{$cleanedTerm}%")
                ->orWhere('sectors', 'like', "%{$cleanedTerm}%")
                ->orWhere('target_zones', 'like', "%{$cleanedTerm}%");
        });
    }
}
