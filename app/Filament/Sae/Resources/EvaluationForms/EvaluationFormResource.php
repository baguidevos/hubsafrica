<?php

namespace App\Filament\Sae\Resources\EvaluationForms;

use App\Enums\FormType;
use App\Enums\FormUnlockStatus;
use App\Filament\Sae\Clusters\ImpactCluster;
use App\Filament\Sae\Resources\EvaluationForms\RelationManagers\UnlocksRelationManager;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\Form;
use App\Models\FormUnlock;
use App\Models\TimelineEvent;
use App\Services\ApplicationFormTemplateService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Packstub\FormBuilder\Filament\FieldBlocks;
use Packstub\FormBuilder\Filament\FormActions;
use Packstub\FormBuilder\Filament\Resources\FormResource;
use Packstub\FormBuilder\Filament\Resources\FormResource\Pages\CreateForm;
use Packstub\FormBuilder\Filament\Resources\FormResource\Pages\EditForm;
use Packstub\FormBuilder\Filament\Resources\FormResource\Pages\ListForms;
use Packstub\FormBuilder\Filament\Resources\FormResource\RelationManagers\SubmissionsRelationManager;
use Packstub\FormBuilder\Filament\Resources\FormResource\RelationManagers\WebhookDeliveriesRelationManager;

class EvaluationFormResource extends FormResource
{
    protected static ?string $slug = 'evaluation-forms';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModel(): string
    {
        return Form::class;
    }

    protected static ?string $cluster = ImpactCluster::class;

    public static function getCluster(): ?string
    {
        return ImpactCluster::class;
    }

    public static function getModelLabel(): string
    {
        return 'Formulaire d\'évaluation';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Formulaires & Enquêtes';
    }

    public static function getNavigationLabel(): string
    {
        return 'Formulaires & Enquêtes';
    }

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-clipboard-document-check';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->tabs([
                        Tab::make('Cadrage Suivi & Évaluation')
                            ->icon('heroicon-o-academic-cap')
                            ->schema(static::seScopeSchema()),
                        Tab::make(__('packstub-form-builder::form-builder.tabs.fields'))
                            ->icon('heroicon-o-list-bullet')
                            ->schema([FieldBlocks::make('fields')]),
                        Tab::make(__('packstub-form-builder::form-builder.tabs.settings'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema(static::settingsSchema()),
                        Tab::make(__('packstub-form-builder::form-builder.tabs.notifications'))
                            ->icon('heroicon-o-bell')
                            ->schema(static::notificationsSchema()),
                        Tab::make(__('packstub-form-builder::form-builder.tabs.design'))
                            ->icon('heroicon-o-paint-brush')
                            ->schema(static::designSchema()),
                        Tab::make(__('packstub-form-builder::form-builder.tabs.embed'))
                            ->icon('heroicon-o-code-bracket')
                            ->schema(static::embedSchema())
                            ->hidden(fn (?Model $record): bool => $record === null),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    protected static function seScopeSchema(): array
    {
        return [
            Section::make('Rattachement au parcours & Type de collecte')
                ->description('Définissez le rôle de ce questionnaire dans le cycle d\'accompagnement des entrepreneurs.')
                ->columns(2)
                ->schema([
                    Select::make('type')
                        ->label('Type de questionnaire')
                        ->options(FormType::class)
                        ->required()
                        ->native(false)
                        ->default(FormType::BASELINE),
                    Select::make('program_id')
                        ->label('Programme associé')
                        ->relationship('program', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Transversal (Tous les programmes)')
                        ->helperText('Laissez vide si le questionnaire s\'applique de manière transversale à toute la structure.'),
                    Select::make('target_level_id')
                        ->label('Niveau / Jalon ciblé (optionnel)')
                        ->relationship('targetLevel', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Aucun niveau spécifique')
                        ->helperText('Ex: Étape 2 - Diagnostic ou Étape 5 - Sortie.'),
                    Toggle::make('is_mandatory')
                        ->label('Questionnaire obligatoire')
                        ->helperText('Si activé, l\'entrepreneur doit impérativement remplir ce questionnaire dans son parcours.')
                        ->default(true),
                    Toggle::make('is_template')
                        ->label('Modèle / Template de candidature réutilisable')
                        ->helperText('Activez pour que ce formulaire apparaisse dans la liste des templates sélectionnables pour les cohortes.')
                        ->default(false),
                ]),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom du questionnaire')
                    ->description(fn (Form $record): string => $record->slug)
                    ->searchable(['name', 'slug'])
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('usage')
                    ->label('Rôle')
                    ->badge()
                    ->state(function (Form $record): string {
                        if ($record->is_standard) {
                            return '⭐️ Standard Hub Africa';
                        }
                        if ($record->is_template || $record->type === FormType::CANDIDATURE) {
                            return '🏛️ Template Candidature';
                        }

                        return '📊 Enquête & Suivi';
                    })
                    ->color(function (Form $record): string {
                        if ($record->is_standard) {
                            return 'warning';
                        }
                        if ($record->is_template || $record->type === FormType::CANDIDATURE) {
                            return 'primary';
                        }

                        return 'gray';
                    }),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('program.name')
                    ->label('Programme')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Transversal')
                    ->sortable(),
                TextColumn::make('unlocks_count')
                    ->label('Assignés')
                    ->counts('unlocks')
                    ->badge()
                    ->color('info')
                    ->alignEnd(),
                TextColumn::make('submissions_count')
                    ->label('Soumissions')
                    ->counts('submissions')
                    ->badge()
                    ->color('success')
                    ->alignEnd(),
                TextColumn::make('completion_rate')
                    ->label('Taux de complétion')
                    ->state(fn (Form $record): string => $record->completionRate().'%')
                    ->badge()
                    ->color(fn (Form $record): string => $record->completionRate() >= 80 ? 'success' : ($record->completionRate() >= 40 ? 'warning' : 'gray'))
                    ->alignEnd(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                IconColumn::make('is_mandatory')
                    ->label('Obligatoire')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Mis à jour')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Type d\'évaluation')
                    ->options(FormType::class),
                SelectFilter::make('program_id')
                    ->label('Programme')
                    ->relationship('program', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->recordActions([
                Action::make('assign_quick')
                    ->label('Assigner')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->modalHeading('Assigner ce questionnaire')
                    ->modalDescription('Sélectionnez une cohorte ou des entrepreneurs pour leur débloquer ce formulaire.')
                    ->modalSubmitActionLabel('Débloquer & Notifier')
                    ->form([
                        Radio::make('mode')
                            ->label("Mode d'assignation")
                            ->options([
                                'cohort' => 'Toute une cohorte',
                                'custom' => 'Sélection individuelle d\'entrepreneurs',
                            ])
                            ->default('cohort')
                            ->live(),
                        Select::make('cohort_id')
                            ->label('Cohorte cible')
                            ->options(fn () => Cohort::query()->pluck('name', 'id'))
                            ->visible(fn ($get) => $get('mode') === 'cohort')
                            ->required(fn ($get) => $get('mode') === 'cohort')
                            ->searchable()
                            ->preload(),
                        Select::make('entrepreneurs')
                            ->label('Entrepreneurs')
                            ->options(function () {
                                return Entrepreneur::query()->get()->mapWithKeys(function ($e) {
                                    return [$e->id => "{$e->full_name} ({$e->email})"];
                                });
                            })
                            ->multiple()
                            ->visible(fn ($get) => $get('mode') === 'custom')
                            ->required(fn ($get) => $get('mode') === 'custom')
                            ->searchable(),
                        DatePicker::make('due_date')
                            ->label('Date limite de réponse')
                            ->default(now()->addDays(14))
                            ->required(),
                        Textarea::make('note')
                            ->label('Consignes particulières (optionnel)')
                            ->placeholder('Ex: Merci de renseigner cette évaluation d\'entrée avant le prochain jalon.'),
                    ])
                    ->action(function (Form $record, array $data): void {
                        $entrepreneurIds = collect();

                        if ($data['mode'] === 'cohort') {
                            $cohort = Cohort::find($data['cohort_id']);
                            if ($cohort) {
                                $entrepreneurIds = $cohort->enrollments()->pluck('entrepreneur_id')->filter()->unique();
                            }
                        } else {
                            $entrepreneurIds = collect($data['entrepreneurs'])->filter();
                        }

                        if ($entrepreneurIds->isEmpty()) {
                            Notification::make()
                                ->title('Aucun entrepreneur sélectionné')
                                ->warning()
                                ->send();

                            return;
                        }

                        $count = 0;
                        $saeId = $record->getAttribute('sae_id') ?? auth()->user()?->sae_id;

                        foreach ($entrepreneurIds as $entId) {
                            FormUnlock::updateOrCreate(
                                [
                                    'form_id' => $record->getKey(),
                                    'entrepreneur_id' => $entId,
                                ],
                                [
                                    'sae_id' => $saeId,
                                    'unlocked_by_id' => auth()->id(),
                                    'due_date' => $data['due_date'],
                                    'note' => $data['note'] ?? null,
                                    'status' => FormUnlockStatus::EN_ATTENTE,
                                ]
                            );

                            $entrepreneur = Entrepreneur::with('user')->find($entId);

                            if ($entrepreneur?->user) {
                                Notification::make()
                                    ->title('Nouveau formulaire à remplir : '.$record->name)
                                    ->body('Date limite : '.Carbon::parse($data['due_date'])->format('d/m/Y').($data['note'] ? " — Consigne : {$data['note']}" : ''))
                                    ->icon('heroicon-o-clipboard-document-check')
                                    ->warning()
                                    ->sendToDatabase($entrepreneur->user);
                            }

                            if ($entrepreneur) {
                                TimelineEvent::create([
                                    'sae_id' => $saeId,
                                    'entrepreneur_id' => $entrepreneur->id,
                                    'title' => 'Questionnaire assigné : '.$record->name,
                                    'description' => "Le questionnaire '{$record->name}' a été assigné par l'équipe d'accompagnement.",
                                    'type' => 'EVALUATION',
                                    'event_date' => now(),
                                ]);
                            }

                            $count++;
                        }

                        Notification::make()
                            ->title("{$count} entrepreneur(s) assigné(s) avec succès")
                            ->body('Les entrepreneurs ont reçu une notification dans leur espace.')
                            ->success()
                            ->send();
                    }),
                Action::make('duplicateAsTemplate')
                    ->label('Créer template SAE')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->tooltip('Dupliquer ce formulaire comme template personnalisable pour votre structure')
                    ->action(function (Form $record, ApplicationFormTemplateService $service) {
                        $saeId = Filament::getTenant()?->id;
                        if (! $saeId) {
                            return;
                        }
                        $newForm = $service->duplicateAsSaeTemplate($record, $saeId);
                        Notification::make()
                            ->title('Template créé !')
                            ->body("Le template « {$newForm->name} » a été ajouté à votre structure.")
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->visible(fn (Form $record) => ! $record->is_standard),
                FormActions::share(),
                Action::make('open')
                    ->label(__('packstub-form-builder::form-builder.actions.open_page'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Form $record): ?string => $record->isPrivate() ? $record->shareUrl() : $record->pageUrl(), shouldOpenInNewTab: true)
                    ->visible(fn (Form $record): bool => $record->pageUrl() !== null),
                ActionGroup::make([
                    FormActions::duplicate(),
                    FormActions::export(),
                    DeleteAction::make()
                        ->visible(fn (Form $record) => ! $record->is_standard),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $saeId = Filament::getTenant()?->id;

        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->where(function (Builder $query) use ($saeId) {
                $query->where('is_standard', true);
                if ($saeId) {
                    $query->orWhere('sae_id', $saeId);
                }
            });
    }

    public static function getRelations(): array
    {
        return [
            UnlocksRelationManager::class,
            SubmissionsRelationManager::class,
            WebhookDeliveriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForms::route('/'),
            'create' => CreateForm::route('/create'),
            'edit' => EditForm::route('/{record}/edit'),
        ];
    }
}
