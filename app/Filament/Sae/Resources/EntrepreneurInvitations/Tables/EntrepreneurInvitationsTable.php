<?php

namespace App\Filament\Sae\Resources\EntrepreneurInvitations\Tables;

use App\Enums\EntrepreneurInvitationStatus;
use App\Mail\EntrepreneurDirectInvitationMail;
use App\Models\EntrepreneurInvitation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class EntrepreneurInvitationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Porteur invité')
                    ->weight('bold')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable(['first_name', 'last_name']),

                TextColumn::make('email')
                    ->label('Email d\'invitation')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('cohort.name')
                    ->label('Cohorte cible')
                    ->badge()
                    ->color('info')
                    ->placeholder('Hors cohorte')
                    ->sortable(),

                TextColumn::make('project_name')
                    ->label('Projet')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Envoyée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->options(EntrepreneurInvitationStatus::class),

                SelectFilter::make('cohort_id')
                    ->label('Cohorte')
                    ->relationship('cohort', 'name'),
            ])
            ->recordActions([
                Action::make('send_email')
                    ->label('Renvoyer email')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Renvoyer l\'email d\'invitation')
                    ->modalDescription(fn (EntrepreneurInvitation $record) => "L'email d'invitation avec le lien sécurisé sera réexpédié à {$record->email}.")
                    ->visible(fn (EntrepreneurInvitation $record) => $record->isPending())
                    ->action(function (EntrepreneurInvitation $record) {
                        Mail::to($record->email)
                            ->queue(new EntrepreneurDirectInvitationMail($record));

                        Notification::make()
                            ->title('Email renvoyé')
                            ->body("L'invitation a été réexpédiée à {$record->email}.")
                            ->success()
                            ->send();
                    }),

                Action::make('copy_link')
                    ->label('Lien d\'invitation')
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->action(function (EntrepreneurInvitation $record) {
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
