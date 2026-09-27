<?php

namespace App\Filament\Sae\Resources\CoachInvitations\Tables;

use App\Enums\CoachInvitationStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                Action::make('copy_link')
                    ->label('Lien')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->action(function ($record) {
                        $url = url('/register/coach?token=' . $record->token);
                        Notification::make()
                            ->title('Lien d\'invitation généré')
                            ->body($url)
                            ->info()
                            ->send();
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
