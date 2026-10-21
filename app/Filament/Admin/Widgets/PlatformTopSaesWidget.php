<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Sae;
use Filament\Widgets\Widget;

class PlatformTopSaesWidget extends Widget
{
    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.admin.widgets.platform-top-saes-widget';

    public function getSaes(): array
    {
        $saes = Sae::withCount(['projects', 'coaches', 'cohorts', 'programs'])
            ->orderByDesc('projects_count')
            ->limit(8)
            ->get();

        return $saes->map(function (Sae $sae) {
            $location = $sae->provenance_label;
            if ($location === 'Non renseigné' || empty($location)) {
                $location = $sae->address ?: 'Réseau Hubs Africa';
            }

            return [
                'id' => $sae->id,
                'name' => $sae->name,
                'slug' => $sae->slug,
                'logo_url' => $sae->logo_url,
                'origin_label' => $sae->origin_label,
                'location' => $location,
                'projects_count' => $sae->projects_count,
                'coaches_count' => $sae->coaches_count,
                'cohorts_count' => $sae->cohorts_count,
                'status' => $sae->status ?? 'ACTIVE',
                'url' => url("/sae/{$sae->slug}"),
            ];
        })->toArray();
    }
}
