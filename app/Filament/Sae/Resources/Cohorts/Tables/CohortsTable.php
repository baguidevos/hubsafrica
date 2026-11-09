<?php

namespace App\Filament\Sae\Resources\Cohorts\Tables;

use App\Enums\CohortStatus;
use App\Enums\EntrepreneurInvitationStatus;
use App\Mail\EntrepreneurDirectInvitationMail;
use App\Models\Cohort;
use App\Models\EntrepreneurInvitation;
use App\Services\AccompanimentTemplateService;
use App\Services\AttendanceService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class CohortsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Cohorte / Promotion')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('program.name')
                    ->label('Programme')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('applicationForm.name')
                    ->label('Formulaire')
                    ->badge()
                    ->color(fn ($record) => $record?->applicationForm?->is_standard ? 'warning' : 'primary')
                    ->placeholder('Modèle standard Hubs Africa')
                    ->limit(22)
                    ->tooltip(fn ($record) => $record?->applicationForm?->name)
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('capacity')
                    ->label('Capacité')
                    ->suffix(' startups')
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Lancement')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Clôture')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(CohortStatus::class),

                SelectFilter::make('program_id')
                    ->label('Programme')
                    ->relationship('program', 'name'),
            ])
            ->recordActions([
                Action::make('applyTemplate')
                    ->label('Template')
                    ->icon('heroicon-o-sparkles')
                    ->color('emerald')
                    ->tooltip('Déployer le pack méthodologique sur cette cohorte')
                    ->slideOver()
                    ->modalHeading(fn (Cohort $record) => "Appliquer un modèle d'accompagnement à {$record->name}")
                    ->form([
                        Select::make('template_key')
                            ->label('Modèle méthodologique')
                            ->options(fn (AccompanimentTemplateService $service) => $service->asSelectOptions())
                            ->default('cube-saei-kpital')
                            ->required(),
                    ])
                    ->action(function (Cohort $record, array $data, AccompanimentTemplateService $service) {
                        $sae = filament()->getTenant();
                        $program = $record->program;
                        if (! $program) {
                            Notification::make()->title('Erreur')->body('La cohorte doit être rattachée à un programme.')->danger()->send();

                            return;
                        }

                        $summary = $service->apply($data['template_key'], $sae, $program, $record);

                        Notification::make()
                            ->title('Modèle méthodologique appliqué !')
                            ->success()
                            ->body("Cohorte {$record->name} synchronisée : {$summary['phases']} phases et {$summary['startup_deliverables']} livrables générés.")
                            ->send();
                    }),
                Action::make('syncPhases')
                    ->label('Phases')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->tooltip('Synchroniser les phases depuis le programme')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->syncPhasesFromProgram();
                        Notification::make()
                            ->title('Jalons synchronisés')
                            ->body("Les phases du programme ont été copiées pour {$record->name}.")
                            ->success()
                            ->send();
                    }),
                Action::make('exportAttendanceMatrix')
                    ->label('Présences Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->tooltip('Exporter la matrice Excel des présences de toutes les séances de cette cohorte')
                    ->action(function (Cohort $record, AttendanceService $service) {
                        return $service->downloadCohortAttendanceMatrixCsv($record);
                    }),
                Action::make('copyJoinLink')
                    ->label('Lien direct')
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->tooltip('Générer et copier le lien direct pour candidater à cette cohorte')
                    ->modalHeading(fn (Cohort $record) => "Lien de candidature directe — {$record->name}")
                    ->modalDescription("Partagez ce lien avec vos porteurs de projet. Les personnes sans compte seront invitées à s'inscrire avant d'accéder au formulaire de candidature pré-verrouillé sur cette cohorte.")
                    ->form([
                        TextInput::make('join_url')
                            ->label('Lien de candidature directe')
                            ->default(fn (Cohort $record) => route('public.cohort.join.clean', $record->id))
                            ->readOnly()
                            ->helperText('Ce lien amène l\'entrepreneur à se connecter ou s\'inscrire, puis le redirige vers le formulaire de cette cohorte.'),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->extraModalFooterActions([
                        Action::make('copyToClipboard')
                            ->label('Copier le lien')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->color('primary')
                            ->action(function ($livewire, Cohort $record) {
                                $url = route('public.cohort.join.clean', $record->id);
                                $livewire->js("window.navigator.clipboard.writeText('{$url}')");
                                Notification::make()
                                    ->title('Lien copié !')
                                    ->body("Le lien direct pour la cohorte {$record->name} a été copié dans le presse-papier.")
                                    ->success()
                                    ->send();
                            }),
                    ]),
                Action::make('inviteEntrepreneur')
                    ->label('Inviter')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->tooltip('Inviter directement un entrepreneur dans cette promotion')
                    ->modalHeading(fn (Cohort $record) => "Inviter un entrepreneur dans {$record->name}")
                    ->modalDescription('Génère un lien d\'invitation sécurisé (/join/{token}) rattaché à cette cohorte et transmet un email au porteur de projet.')
                    ->modalSubmitActionLabel('Envoyer l\'invitation')
                    ->form([
                        TextInput::make('email')
                            ->label('Adresse email du porteur')
                            ->email()
                            ->required(),
                        TextInput::make('first_name')
                            ->label('Prénom')
                            ->nullable(),
                        TextInput::make('last_name')
                            ->label('Nom')
                            ->nullable(),
                        TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->nullable(),
                        TextInput::make('project_name')
                            ->label('Nom du projet (Optionnel)')
                            ->nullable(),
                    ])
                    ->action(function (Cohort $record, array $data): void {
                        $invitation = EntrepreneurInvitation::create([
                            'sae_id' => $record->sae_id ?: filament()->getTenant()?->id,
                            'cohort_id' => $record->id,
                            'email' => $data['email'],
                            'first_name' => $data['first_name'] ?? null,
                            'last_name' => $data['last_name'] ?? null,
                            'phone' => $data['phone'] ?? null,
                            'project_name' => $data['project_name'] ?? null,
                            'status' => EntrepreneurInvitationStatus::PENDING,
                        ]);

                        Mail::to($invitation->email)
                            ->queue(new EntrepreneurDirectInvitationMail($invitation));

                        $invitation->makeInvitationNotification()->send();
                    }),
                ViewAction::make(),
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }
}
