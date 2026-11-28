<?php

namespace App\Filament\Sae\Resources\Entrepreneurs\Pages;

use App\Filament\Sae\Resources\EntrepreneurInvitations\Pages\CreateEntrepreneurInvitation;
use App\Filament\Sae\Resources\Entrepreneurs\EntrepreneurResource;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Services\EntrepreneurExportService;
use App\Support\EntrepreneurProjectReminderService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListEntrepreneurs extends ListRecords
{
    protected static string $resource = EntrepreneurResource::class;

    protected function getHeaderActions(): array
    {
        $reminderService = app(EntrepreneurProjectReminderService::class);
        $tenantId = Filament::getTenant()?->id;

        return [
            Action::make('remind_candidates_without_project')
                ->label('Candidats sans projet')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->badge(fn (): int => $reminderService->candidatesWithoutProjectQuery($tenantId)->count())
                ->badgeColor('warning')
                ->modalHeading('Relancer les candidats sans projet (issus d\'un formulaire)')
                ->modalDescription('Ces entrepreneurs ont déposé au moins une candidature mais n\'ont pas encore de fiche projet. Ils conserveront le statut Candidat et ne seront rattachés à aucune cohorte ni aucun enrollment.')
                ->modalWidth('4xl')
                ->modalSubmitActionLabel('Envoyer les invitations')
                ->schema(function () use ($reminderService, $tenantId): array {
                    $candidates = $reminderService->candidatesWithoutProjectQuery($tenantId)->get();
                    $options = $candidates
                        ->mapWithKeys(fn (Entrepreneur $entrepreneur): array => [
                            $entrepreneur->id => $reminderService->formatCandidateOptionLabel($entrepreneur),
                        ])
                        ->all();

                    return [
                        CheckboxList::make('entrepreneur_ids')
                            ->label("Candidats détectés ({$candidates->count()})")
                            ->options($options)
                            ->default(array_keys($options))
                            ->searchable()
                            ->bulkToggleable()
                            ->required()
                            ->helperText('Décochez les candidats que vous ne souhaitez pas relancer.'),

                        TextInput::make('subject')
                            ->label('Objet de l\'e-mail')
                            ->default($reminderService->defaultCandidateSubject())
                            ->required()
                            ->maxLength(255),

                        RichEditor::make('custom_message')
                            ->label('Message de l\'e-mail')
                            ->default($reminderService->defaultCandidateMessageHtml())
                            ->required()
                            ->columnSpanFull(),
                    ];
                })
                ->action(function (array $data) use ($reminderService, $tenantId): void {
                    $selectedIds = $data['entrepreneur_ids'] ?? [];
                    $candidates = $reminderService->candidatesWithoutProjectQuery($tenantId)
                        ->whereIn('id', $selectedIds)
                        ->get();

                    if ($candidates->isEmpty()) {
                        Notification::make()
                            ->title('Aucun candidat sélectionné')
                            ->warning()
                            ->send();

                        return;
                    }

                    $summary = $reminderService->sendCandidateReminders(
                        entrepreneurs: $candidates,
                        tenant: Filament::getTenant(),
                        subject: $data['subject'] ?? null,
                        messageHtml: $data['custom_message'] ?? null,
                    );

                    Notification::make()
                        ->title('Relances candidats programmées')
                        ->body("{$summary['emailed']} e-mail(s) mis en file d'attente (statut Candidat conservé, sans rattachement de cohorte).")
                        ->success()
                        ->send();
                }),

            Action::make('remind_direct_without_project')
                ->label('Hors candidature sans projet')
                ->icon('heroicon-o-envelope')
                ->color('info')
                ->badge(fn (): int => $reminderService->directEntrepreneursWithoutProjectQuery($tenantId)->count())
                ->badgeColor('info')
                ->modalHeading('Relancer les entrepreneurs sans projet (hors formulaire de candidature)')
                ->modalDescription('Ces entrepreneurs n\'ont pas de candidature enregistrée et pas encore de fiche projet. Vous pouvez voir s\'ils sont déjà liés à un programme et choisir ou non de les rattacher à une cohorte.')
                ->modalWidth('4xl')
                ->modalSubmitActionLabel('Envoyer les relances')
                ->extraModalFooterActions([
                    Action::make('export_direct_without_project_csv')
                        ->label('Exporter en Excel / CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(fn (): StreamedResponse => $reminderService->streamDirectWithoutProjectCsv(
                            saeId: $tenantId,
                            saeSlug: Filament::getTenant()?->slug ?? Filament::getTenant()?->name,
                        )),
                ])
                ->schema(function () use ($reminderService, $tenantId): array {
                    $initialEntrepreneurs = $reminderService->directEntrepreneursWithoutProjectQuery($tenantId, 'created_at_desc')->get();
                    $initialIds = $initialEntrepreneurs->pluck('id')->all();

                    return [
                        Select::make('sort_by')
                            ->label('Trier la liste par')
                            ->options([
                                'created_at_desc' => 'Date de création (plus récents d\'abord)',
                                'created_at_asc' => 'Date de création (plus anciens d\'abord)',
                                'name_asc' => 'Ordre alphabétique (A → Z)',
                            ])
                            ->default('created_at_desc')
                            ->selectablePlaceholder(false)
                            ->native(false)
                            ->live(),

                        CheckboxList::make('entrepreneur_ids')
                            ->label("Entrepreneurs détectés ({$initialEntrepreneurs->count()})")
                            ->options(function (Get $get) use ($reminderService, $tenantId): array {
                                $sortBy = (string) ($get('sort_by') ?: 'created_at_desc');

                                return $reminderService->directEntrepreneursWithoutProjectQuery($tenantId, $sortBy)
                                    ->get()
                                    ->mapWithKeys(fn (Entrepreneur $entrepreneur): array => [
                                        $entrepreneur->id => $reminderService->formatDirectEntrepreneurOptionLabel($entrepreneur),
                                    ])
                                    ->all();
                            })
                            ->default($initialIds)
                            ->searchable()
                            ->bulkToggleable()
                            ->required()
                            ->helperText('La mention « Programme lié » indique si l\'entrepreneur est déjà rattaché à un programme/cohorte.'),

                        Select::make('cohort_id')
                            ->label('Rattacher à une cohorte (Optionnel)')
                            ->options(fn (): array => $reminderService->activeCohortOptions($tenantId))
                            ->placeholder('Ne pas rattacher à une cohorte — envoyer uniquement le mail')
                            ->searchable()
                            ->preload(),

                        TextInput::make('subject')
                            ->label('Objet de l\'e-mail')
                            ->default($reminderService->defaultDirectSubject())
                            ->required()
                            ->maxLength(255),

                        RichEditor::make('custom_message')
                            ->label('Message de l\'e-mail')
                            ->default($reminderService->defaultDirectMessageHtml())
                            ->required()
                            ->columnSpanFull(),
                    ];
                })
                ->action(function (array $data) use ($reminderService, $tenantId): void {
                    $selectedIds = $data['entrepreneur_ids'] ?? [];
                    $entrepreneurs = $reminderService->directEntrepreneursWithoutProjectQuery($tenantId)
                        ->whereIn('id', $selectedIds)
                        ->get();

                    if ($entrepreneurs->isEmpty()) {
                        Notification::make()
                            ->title('Aucun entrepreneur sélectionné')
                            ->warning()
                            ->send();

                        return;
                    }

                    $cohort = filled($data['cohort_id'] ?? null)
                        ? Cohort::with('program')->find($data['cohort_id'])
                        : null;

                    $summary = $reminderService->sendDirectReminders(
                        entrepreneurs: $entrepreneurs,
                        tenant: Filament::getTenant(),
                        cohort: $cohort,
                        subject: $data['subject'] ?? null,
                        messageHtml: $data['custom_message'] ?? null,
                    );

                    $body = $cohort
                        ? "{$summary['enrolled']} nouveau(x) rattachement(s) à « {$cohort->name} » et {$summary['emailed']} e-mail(s) mis en file d'attente."
                        : "{$summary['emailed']} e-mail(s) de relance mis en file d'attente (sans rattachement à une cohorte).";

                    Notification::make()
                        ->title('Relances programmées')
                        ->body($body)
                        ->success()
                        ->send();
                }),

            Action::make('export_entrepreneurs_csv')
                ->label('Exporter (Excel / CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn (): StreamedResponse => app(EntrepreneurExportService::class)->streamEntrepreneursCsv(
                    saeId: $tenantId,
                    saeSlug: Filament::getTenant()?->slug ?? Filament::getTenant()?->name,
                )),

            Action::make('invite')
                ->label('Inviter un entrepreneur')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->url(fn () => CreateEntrepreneurInvitation::getUrl()),

            CreateAction::make()
                ->label('Créer manuellement')
                ->slideOver(),
        ];
    }
}
