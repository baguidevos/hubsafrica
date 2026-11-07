<?php

namespace App\Livewire\Public;

use App\Models\Opportunity;
use App\Models\Sae;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Js;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class OpportunitiesCatalog extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'sae', history: true)]
    public string $selectedSae = 'all';

    #[Url(as: 'secteur', history: true)]
    public string $selectedSector = 'all';

    #[Url(as: 'zone', history: true)]
    public string $selectedZone = 'all';

    #[Url(as: 'statut', history: true)]
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

    #[Js]
    public function resetFilters(): string
    {
        return <<<'JS'
            $wire.search = '';
            $wire.selectedSae = 'all';
            $wire.selectedSector = 'all';
            $wire.selectedZone = 'all';
            $wire.selectedStatus = 'all';
        JS;
    }

    public function render(): View
    {
        $opportunityQuery = Opportunity::withoutGlobalScopes()
            ->with('sae')
            ->published();

        // Filtre Recherche textuelle
        if (filled($this->search)) {
            $opportunityQuery->search($this->search);
        }

        // Filtre SAE
        if ($this->selectedSae !== 'all') {
            $opportunityQuery->where('sae_id', (int) $this->selectedSae);
        }

        // Filtre Secteur (dans le JSON sectors)
        if ($this->selectedSector !== 'all') {
            $opportunityQuery->whereJsonContains('sectors', $this->selectedSector);
        }

        // Filtre Zone géographique (dans le JSON target_zones)
        if ($this->selectedZone !== 'all') {
            $opportunityQuery->whereJsonContains('target_zones', $this->selectedZone);
        }

        // Filtre Statut d'échéance (ouvert vs clôturé)
        if ($this->selectedStatus === 'open') {
            $opportunityQuery->where(function (Builder $subQuery): void {
                $subQuery->whereNull('deadline')
                    ->orWhere('deadline', '>=', now());
            });
        } elseif ($this->selectedStatus === 'closed') {
            $opportunityQuery->whereNotNull('deadline')
                ->where('deadline', '<', now());
        }

        // Tri : les opportunités ouvertes en premier, classées par date limite la plus proche, puis les clôturées
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

        $availableSectors = $this->extractUniqueTags($allPublishedOpportunities, 'sectors');
        $availableZones = $this->extractUniqueTags($allPublishedOpportunities, 'target_zones');

        return view('livewire.public.opportunities-catalog', [
            'opportunities' => $opportunities,
            'participatingSaes' => $participatingSaes,
            'availableSectors' => $availableSectors,
            'availableZones' => $availableZones,
        ]);
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
