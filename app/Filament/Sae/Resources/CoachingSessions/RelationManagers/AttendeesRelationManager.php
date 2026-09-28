<?php

namespace App\Filament\Sae\Resources\CoachingSessions\RelationManagers;

use App\Models\Project;
use App\Services\AttendanceService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class AttendeesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendees';

    protected static ?string $title = 'Startups participantes & Feuille d\'émargement';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-user-group';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->label('Startup / Projet invité')
                    ->options(function () {
                        $session = $this->getOwnerRecord();
                        if ($session->cohort_id && $session->cohort) {
                            $projects = $session->cohort->enrollments()
                                ->with('project')
                                ->get()
                                ->pluck('project.name', 'project.id')
                                ->filter();

                            if ($projects->isNotEmpty()) {
                                return $projects;
                            }
                        }

                        return Project::where('sae_id', $session->sae_id)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required(),

                Toggle::make('attended')
                    ->label('Présence effective à la séance')
                    ->default(true)
                    ->helperText('Cocher si le porteur de projet était présent à l\'atelier'),

                Grid::make(2)->schema([
                    Textarea::make('feedback')
                        ->label('Retour d\'expérience ou note du porteur')
                        ->placeholder('Appréciation ou question soulevée...')
                        ->rows(2),

                    Textarea::make('notes')
                        ->label('Observations de l\'animateur / du coach')
                        ->placeholder('Participation, niveau d\'avancement...')
                        ->rows(2),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('project.name')
                    ->label('Startup')
                    ->weight('bold')
                    ->description(fn ($record) => $record->project?->entrepreneur?->full_name)
                    ->searchable(),

                ToggleColumn::make('attended')
                    ->label('Présence')
                    ->alignCenter(),

                TextColumn::make('notes')
                    ->label('Observations animateur')
                    ->limit(35)
                    ->default('—'),

                TextColumn::make('feedback')
                    ->label('Retour startup')
                    ->limit(35)
                    ->default('—'),
            ])
            ->headerActions([
                Action::make('populateFromCohort')
                    ->label('Peupler depuis la cohorte')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->visible(fn () => filled($this->getOwnerRecord()->cohort_id))
                    ->tooltip('Ajoute automatiquement toutes les startups inscrites dans la promotion')
                    ->action(function (AttendanceService $service) {
                        $count = $service->populateFromCohort($this->getOwnerRecord());
                        Notification::make()
                            ->title($count > 0 ? "{$count} startup(s) ajoutée(s) depuis la cohorte." : 'Toutes les startups de la cohorte sont déjà listées.')
                            ->success()
                            ->send();
                    }),

                Action::make('exportCsv')
                    ->label('Exporter feuille (Excel / CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->tooltip('Télécharge la feuille d\'émargement Excel/CSV pré-remplie')
                    ->action(function (AttendanceService $service) {
                        return $service->downloadSessionAttendanceCsv($this->getOwnerRecord());
                    }),

                Action::make('importCsv')
                    ->label('Importer feuille complétée')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->modalHeading('Importer la feuille d\'émargement (Excel / CSV)')
                    ->modalDescription('Sélectionnez le fichier CSV complété. Les présences (OUI / NON) et observations seront automatiquement synchronisées.')
                    ->modalIcon('heroicon-o-arrow-up-tray')
                    ->schema([
                        FileUpload::make('attendance_file')
                            ->label('Fichier d\'émargement (.csv)')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/csv'])
                            ->disk('local')
                            ->directory('temp-attendance-imports')
                            ->required()
                            ->helperText('Fichier téléchargé depuis la plateforme et annoté dans Excel/Google Sheets.'),
                    ])
                    ->action(function (array $data, AttendanceService $service) {
                        $path = Storage::disk('local')->path($data['attendance_file']);
                        $stats = $service->importSessionAttendanceCsv($this->getOwnerRecord(), $path);
                        @unlink($path);

                        if ($stats['total'] === 0) {
                            Notification::make()
                                ->title('Aucune donnée valide trouvée dans le fichier.')
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Émargement mis à jour avec succès')
                            ->body("{$stats['present']} présent(s) et {$stats['absent']} absent(s) enregistrés ({$stats['updated']} startups synchronisées).")
                            ->success()
                            ->send();
                    }),

                Action::make('attachSignedSheet')
                    ->label('Joindre feuille signée / Livrable')
                    ->icon('heroicon-o-paper-clip')
                    ->color('warning')
                    ->modalHeading('Joindre la feuille d\'émargement signée ou le livrable')
                    ->modalDescription('Téléversez le scan ou fichier signé (PDF, Excel, Scan photo) pour l\'archiver dans la GED de la séance comme livrable officiel certifié.')
                    ->modalIcon('heroicon-o-paper-clip')
                    ->schema([
                        FileUpload::make('sheet_file')
                            ->label('Feuille d\'émargement signée ou document justificatif')
                            ->disk('public')
                            ->directory('coaching-sessions/attendance')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                                'image/jpeg',
                                'image/png',
                            ])
                            ->maxSize(15360)
                            ->required(),
                        TextInput::make('title')
                            ->label('Titre du document / livrable')
                            ->default(fn () => 'Feuille d\'émargement signée - '.($this->getOwnerRecord()->theme ?? 'Séance du '.now()->format('d/m/Y')))
                            ->required(),
                    ])
                    ->action(function (array $data, AttendanceService $service) {
                        $session = $this->getOwnerRecord();
                        $storedPath = $data['sheet_file'];
                        $originalName = basename($storedPath);

                        $service->attachSignedDocument(
                            session: $session,
                            storedPath: $storedPath,
                            originalName: $originalName,
                            title: $data['title'] ?? null,
                            userId: auth()->id()
                        );

                        Notification::make()
                            ->title("Feuille d'émargement archivée dans la GED")
                            ->body('Le document a été rattaché à la séance comme livrable d\'accompagnement.')
                            ->success()
                            ->send();
                    }),

                CreateAction::make()
                    ->label('Pointer une startup')
                    ->slideOver(),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markPresent')
                        ->label('Marquer Présent(s)')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['attended' => true])),

                    BulkAction::make('markAbsent')
                        ->label('Marquer Absent(s)')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['attended' => false])),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
