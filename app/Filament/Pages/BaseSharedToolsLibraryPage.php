<?php

namespace App\Filament\Pages;

use App\Models\AccompanimentTool;
use App\Models\Sae;
use App\Models\SaeType;
use App\Support\AccompanimentToolAccessDecision;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

abstract class BaseSharedToolsLibraryPage extends Page
{
    protected string $view = 'filament.common.shared-tools-library';

    public string $selectedTypeFilter = 'matching';

    public string $search = '';

    #[Computed]
    public function viewerSae(): ?Sae
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Sae ? $tenant : null;
    }

    /**
     * @return Collection<int, SaeType>
     */
    #[Computed]
    public function viewerSaeTypes(): Collection
    {
        $viewerSae = $this->viewerSae();

        if (! $viewerSae) {
            return collect();
        }

        return $viewerSae->types()
            ->orderBy('name')
            ->get(['sae_types.id', 'sae_types.name', 'sae_types.color']);
    }

    /**
     * @return Collection<int, SaeType>
     */
    #[Computed]
    public function availableSaeTypes(): Collection
    {
        return SaeType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'color']);
    }

    /**
     * @return Collection<int, AccompanimentTool>
     */
    #[Computed]
    public function sharedTools(): Collection
    {
        $toolsQuery = $this->buildSharedToolsQuery();
        $searchTerm = trim($this->search);

        if ($searchTerm !== '') {
            $toolsQuery->where(function (Builder $searchQuery) use ($searchTerm): void {
                $searchQuery->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        return $toolsQuery
            ->with(['sae:id,name', 'saeTypes:id,name,color'])
            ->latest('id')
            ->get();
    }

    public function resolveAccessDecision(AccompanimentTool $tool): AccompanimentToolAccessDecision
    {
        return $tool->evaluateAccessFor(auth()->user());
    }

    private function buildSharedToolsQuery(): Builder
    {
        if ($this->selectedTypeFilter === 'all') {
            return AccompanimentTool::withoutGlobalScopes()
                ->where('is_published', true)
                ->where('is_shared_with_network', true);
        }

        $selectedTypeId = ctype_digit($this->selectedTypeFilter)
            ? (int) $this->selectedTypeFilter
            : null;

        return AccompanimentTool::sharedForSae($this->viewerSae(), $selectedTypeId);
    }
}
