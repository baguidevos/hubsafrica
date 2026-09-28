<?php

namespace App\Filament\Sae\Resources\Partners\Tables;

use App\Enums\PartnerType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Partenaire / Institution')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Typologie')
                    ->badge()
                    ->sortable(),

                TextColumn::make('contact_name')
                    ->label('Référent')
                    ->description(fn ($record) => $record->contact_email)
                    ->searchable()
                    ->default('—'),

                TextColumn::make('programs_count')
                    ->counts('programs')
                    ->label('Programmes soutenus')
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),

                TextColumn::make('website')
                    ->label('Site web')
                    ->url(fn ($record) => $record->website)
                    ->openUrlInNewTab()
                    ->limit(25)
                    ->default('—'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Typologie')
                    ->options(PartnerType::class),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
