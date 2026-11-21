<?php

namespace App\Filament\Sae\Resources\AuditLogs\Tables;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                TextColumn::make('user_name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->placeholder('Système'),

                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => AuditAction::labelFor($state))
                    ->color(fn (?string $state): string => AuditAction::colorFor($state))
                    ->searchable(),

                TextColumn::make('entity')
                    ->label('Entité')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('entity_id')
                    ->label('ID')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('details')
                    ->label('Détails')
                    ->limit(60)
                    ->tooltip(fn (?AuditLog $record): ?string => $record?->details)
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('ip_address')
                    ->label('Adresse IP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sae.name')
                    ->label('SAE')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Action')
                    ->options(AuditAction::class),

                SelectFilter::make('entity')
                    ->label('Entité')
                    ->options(fn (): array => AuditLog::query()
                        ->distinct()
                        ->orderBy('entity')
                        ->pluck('entity', 'entity')
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->slideOver()
                    ->schema([
                        Section::make('Détails de l\'événement')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('created_at')
                                        ->label('Date & Heure')
                                        ->dateTime('d/m/Y H:i:s'),
                                    TextEntry::make('user_name')
                                        ->label('Utilisateur')
                                        ->placeholder('Système'),
                                    TextEntry::make('action')
                                        ->label('Action')
                                        ->badge()
                                        ->formatStateUsing(fn (?string $state): string => AuditAction::labelFor($state))
                                        ->color(fn (?string $state): string => AuditAction::colorFor($state)),
                                    TextEntry::make('entity')
                                        ->label('Entité')
                                        ->badge()
                                        ->color('gray'),
                                    TextEntry::make('entity_id')
                                        ->label('Identifiant cible')
                                        ->placeholder('—'),
                                    TextEntry::make('ip_address')
                                        ->label('Adresse IP')
                                        ->placeholder('—'),
                                    TextEntry::make('sae.name')
                                        ->label('SAE')
                                        ->placeholder('Plateforme'),
                                ]),
                                TextEntry::make('details')
                                    ->label('Détails')
                                    ->placeholder('Aucun détail supplémentaire')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
