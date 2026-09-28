<?php

namespace App\Filament\Sae\Resources\Entrepreneurs\Tables;

use App\Enums\EntrepreneurStatus;
use App\Enums\Gender;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EntrepreneursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Entrepreneur')
                    ->weight('bold')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('gender')
                    ->label('Genre')
                    ->badge()
                    ->sortable(),

                TextColumn::make('territory')
                    ->label('Territoire')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('education_level')
                    ->label('Niveau d\'études')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('professional_status')
                    ->label('Situation')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date d\'inscription')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->label('Genre')
                    ->options(Gender::class),

                SelectFilter::make('status')
                    ->label('Statut du dossier')
                    ->options(EntrepreneurStatus::class),

                SelectFilter::make('education_level')
                    ->label('Niveau d\'études')
                    ->options([
                        'Sans diplôme' => 'Sans diplôme',
                        'Bac' => 'Baccalauréat',
                        'Bac+2' => 'Bac+2',
                        'Licence' => 'Licence',
                        'Master' => 'Master',
                        'Doctorat' => 'Doctorat',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
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
