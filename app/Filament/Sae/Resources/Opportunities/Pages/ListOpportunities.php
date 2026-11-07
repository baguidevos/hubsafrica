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
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ListOpportunities extends ListRecords
{
    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importOpportunities')
                ->label('Importer un fichier (JSON / CSV / Excel)')
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
                    $rawFile = $data['file'];
                    $sae = filament()->getTenant();

                    // Résolution du chemin absolu du fichier téléchargé
                    $absoluteFilePath = null;

                    if ($rawFile instanceof TemporaryUploadedFile) {
                        $absoluteFilePath = $rawFile->getRealPath();
                    } elseif (is_array($rawFile)) {
                        $firstFile = reset($rawFile);
                        if ($firstFile instanceof TemporaryUploadedFile) {
                            $absoluteFilePath = $firstFile->getRealPath();
                        } elseif (is_string($firstFile)) {
                            $absoluteFilePath = Storage::disk('local')->exists($firstFile)
                                ? Storage::disk('local')->path($firstFile)
                                : (Storage::disk('public')->exists($firstFile) ? Storage::disk('public')->path($firstFile) : storage_path('app/'.$firstFile));
                        }
                    } elseif (is_string($rawFile)) {
                        $absoluteFilePath = Storage::disk('local')->exists($rawFile)
                            ? Storage::disk('local')->path($rawFile)
                            : (Storage::disk('public')->exists($rawFile) ? Storage::disk('public')->path($rawFile) : storage_path('app/'.$rawFile));
                    }

                    if (! $absoluteFilePath || ! file_exists($absoluteFilePath)) {
                        Notification::make()
                            ->title('Fichier introuvable')
                            ->danger()
                            ->body("Impossible d'accéder au fichier téléchargé sur le serveur.")
                            ->send();

                        return;
                    }

                    try {
                        $summary = $service->importFromFile($absoluteFilePath, $sae);

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
                            $firstErrors = ! empty($summary['errors'])
                                ? implode("\n", array_slice($summary['errors'], 0, 3))
                                : 'Le fichier ne contient aucune opportunité valide ou le format est incorrect.';

                            Notification::make()
                                ->title('Aucune opportunité importée')
                                ->danger()
                                ->body($firstErrors)
                                ->persistent()
                                ->send();
                        }
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('Échec de l\'importation')
                            ->danger()
                            ->body($exception->getMessage())
                            ->persistent()
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
