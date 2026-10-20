<?php

namespace App\Filament\Admin\Resources\Saes\Tables;

use App\Enums\SaeOriginType;
use App\Models\Sae;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SaesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn () => asset('images/logo.png')),

                TextColumn::make('name')
                    ->label('Nom de la SAE')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('types.name')
                    ->label('Types de SAE')
                    ->badge()
                    ->separator(', '),

                TextColumn::make('owner.name')
                    ->label('Responsable')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Aucun propriétaire assigné'),

                TextColumn::make('origin_type')
                    ->label('Origine')
                    ->badge()
                    ->formatStateUsing(fn ($state, Sae $record) => $record->university_name ? "Univ: {$record->university_name}" : ($state?->getLabel() ?? 'Indépendante'))
                    ->toggleable(),

                TextColumn::make('country')
                    ->label('Pays')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Non renseigné')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'SUSPENDED' => 'danger',
                        'INACTIVE' => 'gray',
                        default => 'info',
                    })
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email officiel')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('website')
                    ->label('Site web')
                    ->icon('heroicon-o-globe-alt')
                    ->url(fn (Sae $record): ?string => $record->website)
                    ->openUrlInNewTab()
                    ->placeholder('Non renseigné')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Modifiée le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'ACTIVE' => 'Active',
                        'INACTIVE' => 'Inactive',
                        'SUSPENDED' => 'Suspendue',
                    ]),

                SelectFilter::make('origin_type')
                    ->label('Origine institutionnelle')
                    ->options(SaeOriginType::class),

                SelectFilter::make('types')
                    ->label('Type de SAE')
                    ->relationship('types', 'name'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifier'),
                Action::make('view_public')
                    ->label('Voir en ligne')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Sae $record) => route('public.structure.detail', $record->id))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
