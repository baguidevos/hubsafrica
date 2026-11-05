<?php

namespace App\Filament\Sae\Resources\CoachInvitations\Pages;

use App\Filament\Sae\Resources\CoachInvitations\CoachInvitationResource;
use App\Models\CoachInvitation;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCoachInvitations extends ListRecords
{
    protected static string $resource = CoachInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Inviter un coach')
                ->slideOver()
                ->successNotification(fn (CoachInvitation $record) => $record->makeInvitationNotification()),

            Action::make('generic_link')
                ->label('Lien générique Coach')
                ->icon('heroicon-o-link')
                ->color('warning')
                ->modalHeading('Lien d\'invitation permanent pour coachs')
                ->modalDescription('Partagez ce lien unique avec n\'importe quel coach ou mentor. Chaque personne qui l\'ouvre pourra renseigner ses coordonnées et rejoindre directement votre structure SAE.')
                ->form([
                    TextInput::make('generic_url')
                        ->label('Lien permanent à partager')
                        ->default(fn () => Filament::getTenant()?->getCoachInviteUrl())
                        ->readOnly()
                        ->helperText('Ce lien permanent est réutilisable par l\'ensemble de vos mentors et experts.'),
                ])
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer')
                ->extraModalFooterActions([
                    Action::make('copy_generic_url')
                        ->label('Copier le lien')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('primary')
                        ->action(function ($livewire) {
                            $url = Filament::getTenant()?->getCoachInviteUrl();
                            if ($url) {
                                $livewire->js("window.navigator.clipboard.writeText('{$url}')");
                                Notification::make()
                                    ->title('Lien permanent copié !')
                                    ->body('Le lien d\'invitation générique pour coachs a été copié dans votre presse-papier.')
                                    ->success()
                                    ->send();
                            }
                        }),
                    Action::make('regenerate_token')
                        ->label('Régénérer un nouveau lien')
                        ->icon('heroicon-o-arrow-path')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Régénérer le lien permanent')
                        ->modalDescription('L\'ancien lien générique sera immédiatement désactivé. Les futurs coachs devront utiliser le nouveau lien.')
                        ->action(function () {
                            $sae = Filament::getTenant();
                            if ($sae) {
                                $newUrl = $sae->regenerateCoachInviteToken();
                                Notification::make()
                                    ->title('Lien générique renouvelé')
                                    ->body("Nouveau lien actif : {$newUrl}")
                                    ->success()
                                    ->send();
                            }
                        }),
                ]),
        ];
    }
}
