<?php

namespace App\Livewire\Public;

use App\Models\Level;
use App\Models\Program;
use App\Models\Sae;
use Livewire\Attributes\Js;
use Livewire\Attributes\Json;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProgrammesCatalog extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'sae', history: true)]
    public string $selectedSae = 'all';

    #[Url(as: 'status', history: true)]
    public string $selectedStatus = 'all';

    #[Url(as: 'niveau', history: true)]
    public string $selectedLevel = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedSae(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedLevel(): void
    {
        $this->resetPage();
    }

    /**
     * Action client-side avec l'attribut #[Js] pour réinitialiser les filtres
     * sans déclencher de rechargement serveur superflu.
     */
    #[Js]
    public function resetFilters(): string
    {
        return <<<'JS'
            $wire.search = '';
            $wire.selectedSae = 'all';
            $wire.selectedStatus = 'all';
            $wire.selectedLevel = 'all';
        JS;
    }

    /**
     * Endpoint optimisé avec l'attribut #[Json] renvoyant directement du JSON
     * sans cycle de re-rendering Blade pour les survols ou aperçus dynamiques.
     */
    #[Json]
    public function getProgramQuickStats(int $programId): array
    {
        $program = Program::withCount(['applications', 'cohorts'])->find($programId);

        if (! $program) {
            return ['error' => 'Programme introuvable'];
        }

        return [
            'id' => $program->id,
            'name' => $program->name,
            'capacity' => $program->capacity ?: 20,
            'applications_count' => $program->applications_count,
            'cohorts_count' => $program->cohorts_count,
            'is_open' => $program->status === \App\Enums\ProgramStatus::ACTIF,
        ];
    }

    public function render()
    {
        $query = Program::with(['sae', 'level'])
            ->withCount('cohorts');

        // Filtre Recherche
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
                    ->orWhere('objectives', 'like', "%{$this->search}%")
                    ->orWhereHas('sae', function ($sq) {
                        $sq->where('name', 'like', "%{$this->search}%");
                    });
            });
        }

        // Filtre SAE
        if ($this->selectedSae !== 'all') {
            $query->where('sae_id', (int) $this->selectedSae);
        }

        // Filtre Niveau / Jalon
        if ($this->selectedLevel !== 'all') {
            $query->where('level_id', (int) $this->selectedLevel);
        }

        // Filtre Statut / Inscriptions ouvertes
        $now = now()->toDateString();
        if ($this->selectedStatus === 'open') {
            $query->where('status', \App\Enums\ProgramStatus::ACTIF)
                ->where(function ($q) use ($now) {
                    $q->whereNull('registration_end')
                        ->orWhere('registration_end', '>=', $now);
                });
        } elseif ($this->selectedStatus === 'closed') {
            $query->where(function ($q) use ($now) {
                $q->where('status', '!=', \App\Enums\ProgramStatus::ACTIF)
                    ->orWhere(function ($sub) use ($now) {
                        $sub->whereNotNull('registration_end')
                            ->where('registration_end', '<', $now);
                    });
            });
        }

        $programs = $query->latest()->paginate(9);

        $saes = Sae::orderBy('name')->get(['id', 'name']);
        $levels = Level::orderBy('order_index')->get(['id', 'name']);

        return view('livewire.public.programmes-catalog', [
            'programs' => $programs,
            'saes' => $saes,
            'levels' => $levels,
        ]);
    }
}
