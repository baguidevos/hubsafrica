<?php

namespace App\Filament\Sae\Resources\Cohorts\RelationManagers;

use App\Enums\CoachingSessionStatus;
use App\Enums\CoachingSessionType;
use App\Filament\Sae\Resources\CoachingSessions\CoachingSessionResource;
use App\Models\Coach;
use App\Models\CoachingSession;
use App\Models\CohortPhase;
use App\Services\AttendanceService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CohortSessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'coachingSessions';

    protected static ?string $title = 'Séances & Émargements de la cohorte';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-calendar-days';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Select::make('coach_id')
                        ->label('Coach référent / Animateur')
                        ->options(Coach::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Select::make('cohort_phase_id')
                        ->label('Jalon pédagogique de la promotion')
                        ->options(fn () => CohortPhase::where('cohort_id', $this->getOwnerRecord()->id)->pluck('name', 'id'))
                        ->searchable(),

                    DateTimePicker::make('date')
                        ->label('Date & Heure de la séance')
                        ->native(false)
                        ->default(now())
                        ->required(),

                    TextInput::make('duration')
                        ->label('Durée (en minutes)')
                        ->numeric()
                        ->default(90)
                        ->suffix('min')
                        ->required(),

                    Select::make('type')
                        ->label('Type de séance')
                        ->options(CoachingSessionType::class)
                        ->default(CoachingSessionType::ATELIER->value)
                        ->required(),

                    Select::make('status')
                        ->label('Statut')
                        ->options(CoachingSessionStatus::class)
                        ->default(CoachingSessionStatus::PLANIFIEE->value)
                        ->required(),
                ]),

                TextInput::make('theme')
                    ->label('Thème de l\'atelier ou de la séance')
                    ->placeholder('ex: Structuration du Business Model & Pitch')
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('objectives')
                    ->label('Objectifs pédagogiques visés')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('theme')
            ->columns([
                TextColumn::make('date')
                    ->label('Date & Heure')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('theme')
                    ->label('Thème')
                    ->weight('bold')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('coach.name')
                    ->label('Coach / Animateur')
                    ->default('—'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('presence_stat')
                    ->label('Assiduité')
                    ->badge()
                    ->color(function (CoachingSession $record): string {
                        $total = $record->attendees()->count();
                        if ($total === 0) {
                            return 'gray';
                        }
                        $present = $record->attendees()->where('attended', true)->count();
                        $rate = round(($present / $total) * 100);

                        return $rate >= 75 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                    })
                    ->getStateUsing(function (CoachingSession $record): string {
                        $total = $record->attendees()->count();
                        if ($total === 0) {
                            return 'Non pointé';
                        }
                        $present = $record->attendees()->where('attended', true)->count();
                        $rate = round(($present / $total) * 100);

                        return "{$present}/{$total} ({$rate}%)";
                    }),
            ])
            ->headerActions([
                Action::make('exportCohortMatrix')
                    ->label('Matrice globale (Excel)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->tooltip('Télécharge la matrice Excel complète de toutes les séances et présences de cette cohorte')
                    ->action(function (AttendanceService $service) {
                        return $service->downloadCohortAttendanceMatrixCsv($this->getOwnerRecord());
                    }),

                CreateAction::make()
                    ->label('Planifier une séance')
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data): array {
                        $cohort = $this->getOwnerRecord();
                        $data['cohort_id'] = $cohort->id;
                        $data['sae_id'] = $cohort->sae_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('exportSessionCsv')
                    ->label('Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->tooltip('Télécharge la feuille d\'émargement de cette séance')
                    ->action(function (CoachingSession $record, AttendanceService $service) {
                        return $service->downloadSessionAttendanceCsv($record);
                    }),

                Action::make('importSessionCsv')
                    ->label('Importer')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->modalHeading('Importer la feuille d\'émargement')
                    ->modalDescription('Sélectionnez le fichier CSV complété pour mettre à jour les présences de cette séance.')
                    ->modalIcon('heroicon-o-arrow-up-tray')
                    ->schema([
                        FileUpload::make('attendance_file')
                            ->label('Fichier CSV d\'émargement')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/csv'])
                            ->disk('local')
                            ->directory('temp-attendance-imports')
                            ->required(),
                    ])
                    ->action(function (array $data, CoachingSession $record, AttendanceService $service) {
                        $path = Storage::disk('local')->path($data['attendance_file']);
                        $stats = $service->importSessionAttendanceCsv($record, $path);
                        @unlink($path);

                        if ($stats['total'] === 0) {
                            Notification::make()
                                ->title('Aucune donnée trouvée.')
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Émargement synchronisé')
                            ->body("{$stats['present']} présent(s), {$stats['absent']} absent(s) enregistrés.")
                            ->success()
                            ->send();
                    }),

                Action::make('attachSignedSheet')
                    ->label('Feuille signée')
                    ->icon('heroicon-o-paper-clip')
                    ->color('warning')
                    ->tooltip('Joindre le scan ou fichier signé comme livrable')
                    ->modalHeading('Joindre la feuille signée / livrable')
                    ->modalDescription('Le fichier sera conservé dans la GED de la séance.')
                    ->schema([
                        FileUpload::make('sheet_file')
                            ->label('Document signé (PDF, Excel, Scan)')
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
                            ->label('Titre')
                            ->default(fn (CoachingSession $record) => "Feuille d'émargement signée - ".($record->theme ?? 'Séance du '.now()->format('d/m/Y')))
                            ->required(),
                    ])
                    ->action(function (array $data, CoachingSession $record, AttendanceService $service) {
                        $storedPath = $data['sheet_file'];
                        $service->attachSignedDocument(
                            session: $record,
                            storedPath: $storedPath,
                            originalName: basename($storedPath),
                            title: $data['title'] ?? null,
                            userId: auth()->id()
                        );

                        Notification::make()
                            ->title('Document archivé')
                            ->body('La feuille signée a été jointe à la séance avec succès.')
                            ->success()
                            ->send();
                    }),

                Action::make('viewSession')
                    ->label('Émarger')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->url(fn (CoachingSession $record) => CoachingSessionResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),

                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->defaultSort('date', 'desc');
    }
}
