<?php

namespace App\Filament\Sae\Resources\Cohorts\Pages;

use App\Filament\Sae\Resources\Cohorts\CohortResource;
use App\Models\Cohort;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCohort extends EditRecord
{
    protected static string $resource = CohortResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('copyJoinLink')
                ->label('Lien direct de candidature')
                ->icon('heroicon-o-link')
                ->color('warning')
                ->tooltip('Générer et copier le lien direct pour candidater à cette cohorte')
                ->modalHeading(fn (Cohort $record) => "Lien de candidature directe — {$record->name}")
                ->modalDescription("Partagez ce lien avec vos porteurs de projet. Les personnes sans compte seront invitées à s'inscrire avant d'accéder au formulaire de candidature pré-verrouillé sur cette cohorte. Les personnes déjà inscrites pourront se connecter directement.")
                ->form([
                    TextInput::make('join_url')
                        ->label('Lien d\'invitation directe')
                        ->default(fn (Cohort $record) => route('public.cohort.join', $record->id))
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
                            $url = route('public.cohort.join', $record->id);
                            $livewire->js("window.navigator.clipboard.writeText('{$url}')");
                            Notification::make()
                                ->title('Lien copié !')
                                ->body("Le lien direct pour la cohorte {$record->name} a été copié dans le presse-papier.")
                                ->success()
                                ->send();
                        }),
                ]),
            DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Informations générales';
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-o-information-circle';
    }
}
