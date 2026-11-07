<?php

namespace App\Filament\Sae\Resources\Opportunities\Tables;

use App\Models\Opportunity;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OpportunitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Visuel')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => $record->sae?->logo_url)
                    ->toggleable(),

                TextColumn::make('title')
                    ->label('Opportunité')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Opportunity $record): string => $record->sectors ? implode(' • ', $record->sectors) : 'Général'),

                TextColumn::make('status')
                    ->label('État')
                    ->badge()
                    ->getStateUsing(fn (Opportunity $record): string => $record->isClosed() ? 'Clôturé' : 'Ouvert')
                    ->color(fn (Opportunity $record): string => $record->isClosed() ? 'danger' : 'success')
                    ->icon(fn (Opportunity $record): string => $record->isClosed() ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle'),

                TextColumn::make('target_zones')
                    ->label('Zones')
                    ->badge()
                    ->color('info')
                    ->separator(',')
                    ->limitList(2)
                    ->default('Panafricain'),

                TextColumn::make('deadline')
                    ->label('Date limite')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (Opportunity $record): string => $record->getDeadlineStatusLabel())
                    ->color(fn (Opportunity $record): string => $record->isClosed() ? 'danger' : 'gray'),

                IconColumn::make('is_active')
                    ->label('Visible Front')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Publié le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('open')
                    ->label('Candidatures ouvertes uniquement')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)->where(fn ($q) => $q->whereNull('deadline')->orWhere('deadline', '>=', now()))),

                Filter::make('closed')
                    ->label('Clôturées uniquement')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', false)->orWhere(fn ($q) => $q->whereNotNull('deadline')->where('deadline', '<', now()))),

                TernaryFilter::make('is_active')
                    ->label('Statut de publication')
                    ->placeholder('Toutes les opportunités')
                    ->trueLabel('Publiées uniquement')
                    ->falseLabel('Brouillons / Masquées'),
            ])
            ->recordActions([
                Action::make('view_front')
                    ->label('Voir sur le site')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Opportunity $record): string => route('public.opportunities.detail', ['slug' => $record->slug]))
                    ->openUrlInNewTab(),
                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
