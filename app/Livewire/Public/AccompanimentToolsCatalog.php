<?php

namespace App\Livewire\Public;

use App\Enums\AccompanimentToolAccessLevel;
use App\Enums\AccompanimentToolFormat;
use App\Models\AccompanimentTool;
use App\Models\Sae;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class AccompanimentToolsCatalog extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedSaeId = '';

    public string $selectedFormat = '';

    public string $selectedAccessLevel = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedSaeId(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedFormat(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedAccessLevel(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'selectedSaeId', 'selectedFormat', 'selectedAccessLevel']);
        $this->resetPage();
    }

    public function render(): View
    {
        $tools = AccompanimentTool::query()
            ->with('sae')
            ->where('is_published', true)
            ->when(filled($this->search), function ($query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function ($subQuery) use ($term): void {
                    $subQuery->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('category', 'like', $term);
                });
            })
            ->when(filled($this->selectedSaeId), fn ($query) => $query->where('sae_id', (int) $this->selectedSaeId))
            ->when(filled($this->selectedFormat), fn ($query) => $query->where('format', $this->selectedFormat))
            ->when(filled($this->selectedAccessLevel), fn ($query) => $query->where('access_level', $this->selectedAccessLevel))
            ->latest()
            ->paginate(12);

        $saes = Sae::query()
            ->whereHas('accompanimentTools', fn ($query) => $query->where('is_published', true))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.public.accompaniment-tools-catalog', [
            'tools' => $tools,
            'saes' => $saes,
            'formats' => AccompanimentToolFormat::cases(),
            'accessLevels' => AccompanimentToolAccessLevel::cases(),
            'currentUser' => auth()->user(),
        ]);
    }
}
