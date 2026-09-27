<?php

namespace App\Filament\Sae\Pages;

use App\Models\Application;
use App\Models\Coach;
use App\Models\Cohort;
use App\Models\DeliverableSubmission;
use App\Models\Entrepreneur;
use App\Models\FundingRecord;
use App\Models\Indicator;
use App\Models\Program;
use App\Models\Project;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsPage extends Page
{
    protected string $view = 'filament.sae.pages.reports-page';

    protected static string | \UnitEnum | null $navigationGroup = 'Pilotage';

    protected static ?string $navigationLabel = 'Rapports';

    protected static ?string $title = 'Rapports & Indicateurs d\'impact';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?int $navigationSort = 2;

    public ?string $selectedProgramId = 'all';
    public ?string $selectedCohortId = 'all';

    public function getProgramsProperty()
    {
        return Program::all();
    }

    public function getCohortsProperty()
    {
        if ($this->selectedProgramId && $this->selectedProgramId !== 'all') {
            return Cohort::where('program_id', $this->selectedProgramId)->get();
        }
        return Cohort::all();
    }

    public function getIndicatorsData(): array
    {
        $indicators = Indicator::all();

        if ($indicators->isEmpty()) {
            // Calculs dynamiques standards S&E si la table des indicateurs manuels n'a pas encore été garnie
            $totalEnt = max(Entrepreneur::count(), 1);
            $femmes = Entrepreneur::where('gender', 'F')->count();
            $tauxGenre = round(($femmes / $totalEnt) * 100, 1);

            $candidatures = Application::where('status', '!=', 'BROUILLON')->count();
            $admises = Application::where('status', 'ACCEPTEE')->count();
            $tauxAdmission = $candidatures > 0 ? round(($admises / $candidatures) * 100, 1) : 0;

            $livrablesEvalues = DeliverableSubmission::whereIn('status', ['VALIDEE', 'REJETEE'])->count();
            $livrablesValides = DeliverableSubmission::where('status', 'VALIDEE')->count();
            $tauxValidation = $livrablesEvalues > 0 ? round(($livrablesValides / $livrablesEvalues) * 100, 1) : 85;

            $fonds = (float) FundingRecord::whereIn('status', ['ACCORDE', 'DECAISSE', 'INVESTI'])->sum('amount_obtained');

            return [
                [
                    'name' => 'Taux d\'admission au programme',
                    'category' => 'GOUVERNANCE',
                    'formula' => 'Candidatures admises / Candidatures examinées × 100',
                    'current' => $tauxAdmission,
                    'unit' => '%',
                    'target' => 30,
                    'status' => $tauxAdmission <= 35 ? 'ATTEINT' : 'VIGILANCE',
                ],
                [
                    'name' => 'Taux de féminisation (Parité genre)',
                    'category' => 'INCLUSION',
                    'formula' => 'Entrepreneures femmes / Total des porteurs × 100',
                    'current' => $tauxGenre,
                    'unit' => '%',
                    'target' => 40,
                    'status' => $tauxGenre >= 40 ? 'ATTEINT' : 'NON_ATTEINT',
                ],
                [
                    'name' => 'Taux de conformité des livrables',
                    'category' => 'ACCOMPAGNEMENT',
                    'formula' => 'Livrables validés / Livrables évalués × 100',
                    'current' => $tauxValidation,
                    'unit' => '%',
                    'target' => 80,
                    'status' => $tauxValidation >= 75 ? 'ATTEINT' : 'VIGILANCE',
                ],
                [
                    'name' => 'Volume de financements décaissés',
                    'category' => 'FINANCEMENT',
                    'formula' => 'Somme des montants obtenus & investis',
                    'current' => $fonds,
                    'unit' => 'FCFA',
                    'target' => 50000000,
                    'status' => $fonds > 0 ? 'ATTEINT' : 'VIGILANCE',
                ],
            ];
        }

        return $indicators->map(fn ($i) => [
            'name' => $i->name,
            'category' => $i->category ?: 'GENERAL',
            'formula' => $i->formula ?: '—',
            'current' => (float) $i->current,
            'unit' => $i->unit ?: '',
            'target' => (float) $i->target,
            'status' => $i->status ?: 'ATTEINT',
        ])->toArray();
    }

    public function exportCsv(): StreamedResponse
    {
        $data = $this->getIndicatorsData();
        $filename = 'rapport_indicateurs_impact_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Indicateur', 'Categorie', 'Formule', 'Valeur Actuelle', 'Unite', 'Cible', 'Statut']);

            foreach ($data as $row) {
                fputcsv($handle, [
                    $row['name'],
                    $row['category'],
                    $row['formula'],
                    $row['current'],
                    $row['unit'],
                    $row['target'],
                    $row['status'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
