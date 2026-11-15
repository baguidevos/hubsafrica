<?php

namespace App\Filament\Admin\Pages;

use App\Models\Sae;
use App\Support\SaeAiFeatures;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class SaeAiSettingsPage extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?string $navigationLabel = 'Gouvernance IA & MCP';

    protected static ?string $title = 'Activation & Gouvernance IA des SAE';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.admin.pages.sae-ai-settings-page';

    public string $search = '';

    public ?int $selectedSaeId = null;

    /**
     * @var array<string, mixed>
     */
    public array $features = [];

    public function mount(): void
    {
        $firstSae = Sae::orderBy('name')->first();

        if ($firstSae) {
            $this->selectSae($firstSae->id);
        } else {
            $this->features = SaeAiFeatures::defaults();
        }
    }

    /**
     * @return Collection<int, Sae>
     */
    public function getSaesProperty(): Collection
    {
        return Sae::query()
            ->when(
                filled($this->search),
                fn ($query) => $query->where('name', 'like', '%'.trim($this->search).'%')
            )
            ->orderBy('name')
            ->get();
    }

    public function getSelectedSaeProperty(): ?Sae
    {
        return $this->selectedSaeId ? Sae::find($this->selectedSaeId) : null;
    }

    public function selectSae(int $saeId): void
    {
        $sae = Sae::find($saeId);

        if (! $sae) {
            return;
        }

        $this->selectedSaeId = $sae->id;
        $this->features = $sae->getAiFeatures();
    }

    public function applyPreset(string $preset): void
    {
        $this->features = match ($preset) {
            'all' => SaeAiFeatures::allEnabled(),
            'readonly' => SaeAiFeatures::readOnlyEnabled(),
            default => SaeAiFeatures::defaults(),
        };
    }

    public function save(): void
    {
        $sae = $this->selectedSae;

        if (! $sae) {
            return;
        }

        $normalized = SaeAiFeatures::resolve($this->features);
        $sae->update(['ai_features' => $normalized]);
        $this->features = $normalized;

        Notification::make()
            ->title('Configuration IA enregistrée')
            ->body("Les permissions IA / MCP de « {$sae->name} » ont été mises à jour.")
            ->success()
            ->send();
    }
}
