<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Opportunity;
use App\Models\Sae;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class OpportunitiesCatalog extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true, except: '')]
    public string $search = '';

    #[Url(as: 'sae', history: true, except: 'all')]
    public string $selectedSae = 'all';

    #[Url(as: 'secteur', history: true, except: 'all')]
    public string $selectedSector = 'all';

    #[Url(as: 'zone', history: true, except: 'all')]
    public string $selectedZone = 'all';

    #[Url(as: 'statut', history: true, except: 'all')]
    public string $selectedStatus = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedSae(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedSector(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedZone(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Réinitialise l'ensemble des filtres vers leur valeur initiale par défaut.
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->selectedSae = 'all';
        $this->selectedSector = 'all';
        $this->selectedZone = 'all';
        $this->selectedStatus = 'all';
        $this->resetPage();
    }

    /**
     * Indique si un ou plusieurs filtres personnalisés sont actifs.
     */
    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || $this->selectedSae !== 'all'
            || $this->selectedSector !== 'all'
            || $this->selectedZone !== 'all'
            || $this->selectedStatus !== 'all';
    }

    public function render(): View
    {
        $opportunityQuery = Opportunity::withoutGlobalScopes()->with('sae');

        $this->applyFiltersToQuery($opportunityQuery);

        // Tri : opportunités ouvertes en premier classées par date limite, puis clôturées
        $opportunities = $opportunityQuery
            ->orderByRaw('CASE WHEN deadline IS NOT NULL AND deadline < ? THEN 1 ELSE 0 END ASC', [now()])
            ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('deadline', 'asc')
            ->latest('created_at')
            ->paginate(9);

        $participatingSaes = Sae::whereHas('opportunities', function (Builder $subQuery): void {
            $subQuery->published();
        })->orderBy('name')->get(['id', 'name']);

        $allPublishedOpportunities = Opportunity::withoutGlobalScopes()
            ->published()
            ->get(['sectors', 'target_zones']);

        return view('livewire.public.opportunities-catalog', [
            'opportunities' => $opportunities,
            'participatingSaes' => $participatingSaes,
            'availableSectors' => $this->extractUniqueTags($allPublishedOpportunities, 'sectors'),
            'availableZones' => $this->extractUniqueTags($allPublishedOpportunities, 'target_zones'),
            'hasActiveFilters' => $this->hasActiveFilters(),
        ]);
    }

    /**
     * Applique les critères de recherche et de filtrage à la requête Eloquent.
     */
    private function applyFiltersToQuery(Builder $query): void
    {
        // 1. Statut (ouvert vs clôturé vs tous)
        if ($this->selectedStatus === 'open') {
            $query->open();
        } elseif ($this->selectedStatus === 'closed') {
            $query->closed();
        } else {
            // Afficher les opportunités actives ou passées visibles
            $query->where(function (Builder $subQuery): void {
                $subQuery->where('is_active', true)
                    ->orWhere(function (Builder $nestedQuery): void {
                        $nestedQuery->whereNotNull('deadline')
                            ->where('deadline', '<', now());
                    });
            });
        }

        // 2. Recherche textuelle
        if (filled($this->search)) {
            $query->search($this->search);
        }

        // 3. Structure SAE
        if ($this->selectedSae !== 'all') {
            $query->where('sae_id', (int) $this->selectedSae);
        }

        // 4. Secteur d'activité
        if ($this->selectedSector !== 'all') {
            $sector = $this->selectedSector;
            $query->where(function (Builder $subQuery) use ($sector): void {
                $subQuery->whereJsonContains('sectors', $sector)
                    ->orWhere('sectors', 'like', '%"'.addcslashes($sector, '%_\\').'"%')
                    ->orWhere('sectors', 'like', '%'.addcslashes($sector, '%_\\').'%');
            });
        }

        // 5. Zone géographique
        if ($this->selectedZone !== 'all') {
            $zone = $this->selectedZone;
            $query->where(function (Builder $subQuery) use ($zone): void {
                $subQuery->whereJsonContains('target_zones', $zone)
                    ->orWhere('target_zones', 'like', '%"'.addcslashes($zone, '%_\\').'"%')
                    ->orWhere('target_zones', 'like', '%'.addcslashes($zone, '%_\\').'%');
            });
        }
    }

    /**
     * Extrait les tags uniques triés par ordre alphabétique depuis les enregistrements JSON.
     *
     * @param  Collection<int, Opportunity>  $opportunities
     * @return array<int, string>
     */
    private function extractUniqueTags(Collection $opportunities, string $field): array
    {
        $tags = [];

        foreach ($opportunities as $opportunity) {
            $values = $opportunity->{$field};
            if (! is_array($values)) {
                continue;
            }

            foreach ($values as $value) {
                $cleanedValue = trim((string) $value);
                if ($cleanedValue !== '' && ! in_array($cleanedValue, $tags, true)) {
                    $tags[] = $cleanedValue;
                }
            }
        }

        sort($tags, SORT_NATURAL | SORT_FLAG_CASE);

        return $tags;
    }
}
