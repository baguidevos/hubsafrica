<?php

namespace App\Filament\Sae\Resources\CoachInvitations\Tables;

use App\Enums\CoachInvitationStatus;
use App\Mail\CoachDirectInvitationMail;
use App\Models\CoachInvitation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class CoachInvitationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email d\'invitation')
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('max_capacity')
                    ->label('Capacité')
                    ->suffix(' startups')
                    ->alignRight()
                    ->sortable(),

                IconColumn::make('is_external')
                    ->label('Externe')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('Expire le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('accepted_at')
                    ->label('Acceptée le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('En attente')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(CoachInvitationStatus::class),
            ])
            ->recordActions([
                Action::make('send_email')
                    ->label('Envoyer email')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Envoyer le lien d\'invitation par email')
                    ->modalDescription(fn (CoachInvitation $record) => "L'email contenant le lien d'activation sécurisé sera transmis à {$record->email}.")
                    ->action(function (CoachInvitation $record) {
                        Mail::to($record->email)
                            ->queue(new CoachDirectInvitationMail($record));

                        Notification::make()
                            ->title('Email transmis')
                            ->body("L'invitation a été envoyée par email à {$record->email}.")
                            ->success()
                            ->send();
                    }),
                Action::make('copy_link')
                    ->label('Lien d\'invitation')
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->action(function (CoachInvitation $record) {
                        $record->makeInvitationNotification()->send();
                    }),
                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
