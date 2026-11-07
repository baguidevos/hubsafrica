<?php

namespace App\Filament\Sae\Resources\Opportunities\Pages;

use App\Filament\Sae\Resources\Opportunities\OpportunityResource;
use App\Services\OpportunityImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListOpportunities extends ListRecords
{
    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importOpportunities')
                ->label('Importer en masse (JSON / CSV / Excel)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Importer des opportunités en lot')
                ->modalDescription('Chargez un fichier .json, .csv ou .xlsx contenant vos opportunités et appels à projets.')
                ->modalSubmitActionLabel('Lancer l\'importation')
                ->slideOver()
                ->form([
                    FileUpload::make('file')
                        ->label('Fichier de données (.json, .csv, .xlsx)')
                        ->disk('local')
                        ->directory('imports/opportunities')
                        ->acceptedFileTypes([
                            'application/json',
                            'text/csv',
                            'text/plain',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->maxSize(10240)
                        ->required()
                        ->helperText('Formats supportés : .json, .csv (séparateur virgule ou point-virgule), ou .xlsx (Excel).'),
                ])
                ->action(function (array $data, OpportunityImportService $service): void {
                    $relativeFilePath = $data['file'];
                    $absoluteFilePath = Storage::disk('local')->path($relativeFilePath);

                    $sae = filament()->getTenant();

                    try {
                        $summary = $service->importFromFile($absoluteFilePath, $sae);

                        @unlink($absoluteFilePath);

                        if ($summary['imported_count'] > 0) {
                            $notification = Notification::make()
                                ->title('Importation réussie !')
                                ->success()
                                ->body("{$summary['imported_count']} opportunité(s) ont été créées et publiées avec succès.");

                            if ($summary['skipped_count'] > 0) {
                                $notification->warning()
                                    ->body("{$summary['imported_count']} importée(s), {$summary['skipped_count']} ignorée(s).");
                            }

                            $notification->send();
                        } else {
                            Notification::make()
                                ->title('Aucune opportunité importée')
                                ->warning()
                                ->body('Vérifiez le format des colonnes ou la validité des données du fichier.')
                                ->send();
                        }
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('Échec de l\'importation')
                            ->danger()
                            ->body($exception->getMessage())
                            ->send();
                    }
                }),

            CreateAction::make()
                ->label('Publier une opportunité')
                ->icon('heroicon-o-plus')
                ->slideOver(),
        ];
    }
}
