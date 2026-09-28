<?php

namespace App\Filament\Sae\Resources\Coaches\Pages;

use App\Enums\CoachInvitationStatus;
use App\Filament\Sae\Resources\Coaches\CoachResource;
use App\Models\CoachInvitation;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListCoaches extends ListRecords
{
    protected static string $resource = CoachResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('invite_coach')
                ->label('Générer un lien d\'invitation')
                ->icon('heroicon-o-link')
                ->color('warning')
                ->modalHeading('Inviter un nouveau coach')
                ->modalDescription('Renseignez les coordonnées du coach. Un lien d\'invitation unique sera généré avec bouton de copie immédiate.')
                ->form([
                    TextInput::make('name')
                        ->label('Nom complet du coach')
                        ->placeholder('Ex: Marie Sarr')
                        ->required(),
                    TextInput::make('email')
                        ->label('Adresse e-mail d\'invitation')
                        ->email()
                        ->placeholder('marie.sarr@coach.sn')
                        ->required(),
                    TextInput::make('max_capacity')
                        ->label('Capacité maximale d\'accompagnement')
                        ->numeric()
                        ->default(8)
                        ->suffix('startups')
                        ->required(),
                    TagsInput::make('specialties')
                        ->label('Spécialités / Compétences')
                        ->placeholder('Ex: Finance, Marketing Digital'),
                ])
                ->action(function (array $data): void {
                    $sae = Filament::getTenant();

                    $invitation = CoachInvitation::create([
                        'sae_id' => $sae?->id ?? auth()->user()?->sae_id,
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'max_capacity' => $data['max_capacity'] ?? 8,
                        'specialties' => $data['specialties'] ?? [],
                        'is_external' => true,
                        'status' => CoachInvitationStatus::PENDING,
                        'expires_at' => now()->addDays(7),
                        'created_by' => auth()->id(),
                    ]);

                    $invitation->makeInvitationNotification()->send();
                }),
            CreateAction::make()->slideOver(),
        ];
    }
}
