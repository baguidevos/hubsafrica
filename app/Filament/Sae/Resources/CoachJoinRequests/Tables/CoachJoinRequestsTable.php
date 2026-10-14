<?php

namespace App\Filament\Sae\Resources\CoachJoinRequests\Tables;

use App\Enums\CoachJoinRequestStatus;
use App\Models\CoachJoinRequest;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CoachJoinRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom du Coach')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->icon('heroicon-o-phone'),

                TextColumn::make('specialties')
                    ->label('Spécialités')
                    ->badge()
                    ->separator(',')
                    ->limitList(3),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Candidaté le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(CoachJoinRequestStatus::class)
                    ->default(CoachJoinRequestStatus::PENDING->value),
            ])
            ->recordActions([
                Action::make('accept')
                    ->label('Accepter & Inviter')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CoachJoinRequest $record) => $record->status === CoachJoinRequestStatus::PENDING)
                    ->form([
                        TextInput::make('max_capacity')
                            ->label('Capacité maximale d\'accompagnement')
                            ->numeric()
                            ->default(5)
                            ->suffix('startups')
                            ->required(),
                        Toggle::make('is_external')
                            ->label('Coach / Mentor externe')
                            ->default(true),
                    ])
                    ->modalHeading('Accepter la candidature du Coach')
                    ->modalDescription(fn (CoachJoinRequest $record) => "Une invitation sécurisée sera immédiatement envoyée par email à {$record->email} avec le lien d'activation.")
                    ->action(function (CoachJoinRequest $record, array $data) {
                        $record->accept(
                            processor: auth()->user(),
                            maxCapacity: (int) ($data['max_capacity'] ?? 5),
                            isExternal: (bool) ($data['is_external'] ?? true)
                        );

                        Notification::make()
                            ->title('Candidature acceptée !')
                            ->body("L'invitation officielle a été générée et envoyée par email à {$record->email}.")
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Refuser')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CoachJoinRequest $record) => $record->status === CoachJoinRequestStatus::PENDING)
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motif du refus')
                            ->placeholder('Ex: Profil ne correspondant pas aux thématiques prioritaires de la cohorte actuelle...')
                            ->rows(3),
                    ])
                    ->modalHeading('Refuser la candidature')
                    ->action(function (CoachJoinRequest $record, array $data) {
                        $record->reject(
                            processor: auth()->user(),
                            reason: $data['rejection_reason'] ?? null
                        );

                        Notification::make()
                            ->title('Candidature refusée')
                            ->body('Le dossier a été marqué comme refusé.')
                            ->warning()
                            ->send();
                    }),

                ViewAction::make()
                    ->slideOver()
                    ->infolist([
                        TextEntry::make('name')->label('Nom complet'),
                        TextEntry::make('email')->label('Email'),
                        TextEntry::make('phone')->label('Téléphone'),
                        TextEntry::make('linkedin_url')->label('LinkedIn')->url(fn ($record) => $record->linkedin_url, shouldOpenInNewTab: true),
                        TextEntry::make('specialties')->label('Domaines d\'expertise')->badge(),
                        TextEntry::make('bio')->label('Bio & Motivations')->columnSpanFull(),
                        TextEntry::make('status')->label('Statut')->badge(),
                        TextEntry::make('rejection_reason')->label('Motif de refus')->visible(fn ($record) => ! empty($record->rejection_reason)),
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
