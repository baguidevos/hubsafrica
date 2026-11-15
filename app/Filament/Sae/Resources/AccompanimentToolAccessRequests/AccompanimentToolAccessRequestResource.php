<?php

namespace App\Filament\Sae\Resources\AccompanimentToolAccessRequests;

use App\Enums\AccompanimentToolRequestStatus;
use App\Filament\Sae\Clusters\OutilsCluster;
use App\Filament\Sae\Resources\AccompanimentToolAccessRequests\Pages\ListAccompanimentToolAccessRequests;
use App\Models\AccompanimentToolAccessRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccompanimentToolAccessRequestResource extends Resource
{
    protected static ?string $model = AccompanimentToolAccessRequest::class;

    protected static ?string $cluster = OutilsCluster::class;

    protected static ?string $navigationLabel = 'Demandes d\'outils';

    protected static ?string $modelLabel = 'Demande d\'accès outil';

    protected static ?string $pluralModelLabel = 'Demandes d\'accès aux outils';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tool.title')
                    ->label('Outil')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Demandeur')
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('request_message')
                    ->label('Motif')
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Demandé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(AccompanimentToolRequestStatus::class),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (AccompanimentToolAccessRequest $record): bool => $record->status !== AccompanimentToolRequestStatus::APPROVED)
                    ->action(function (AccompanimentToolAccessRequest $record): void {
                        $record->update([
                            'status' => AccompanimentToolRequestStatus::APPROVED,
                            'reviewed_by_id' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Accès approuvé')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Refuser')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn (AccompanimentToolAccessRequest $record): bool => $record->status !== AccompanimentToolRequestStatus::REJECTED)
                    ->form([
                        Textarea::make('decision_note')
                            ->label('Motif du refus (optionnel)')
                            ->rows(2),
                    ])
                    ->action(function (AccompanimentToolAccessRequest $record, array $data): void {
                        $record->update([
                            'status' => AccompanimentToolRequestStatus::REJECTED,
                            'decision_note' => $data['decision_note'] ?? null,
                            'reviewed_by_id' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Demande refusée')
                            ->warning()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccompanimentToolAccessRequests::route('/'),
        ];
    }
}
