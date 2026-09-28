<?php

namespace App\Filament\Entrepreneur\Resources\Resources\Tables;

use App\Models\Resource as ResourceModel;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ResourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Stack::make([
                    Split::make([
                        TextColumn::make('type')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'DOCUMENT' => 'Document',
                                'VIDEO' => 'Vidéo',
                                'LIEN_EXTERNE' => 'Lien web',
                                default => $state,
                            })
                            ->icon(fn ($state) => match ($state) {
                                'DOCUMENT' => 'heroicon-m-document-text',
                                'VIDEO' => 'heroicon-m-play-circle',
                                'LIEN_EXTERNE' => 'heroicon-m-globe-alt',
                                default => 'heroicon-m-book-open',
                            })
                            ->colors([
                                'info' => 'DOCUMENT',
                                'danger' => 'VIDEO',
                                'warning' => 'LIEN_EXTERNE',
                            ]),

                        TextColumn::make('category')
                            ->badge()
                            ->color('gray')
                            ->default('Général')
                            ->alignEnd(),
                    ]),

                    TextColumn::make('title')
                        ->weight('bold')
                        ->size(TextSize::Large)
                        ->searchable()
                        ->sortable(),

                    TextColumn::make('description')
                        ->color('gray')
                        ->lineClamp(3)
                        ->searchable()
                        ->placeholder('Aucune description fournie.'),

                    Split::make([
                        TextColumn::make('level.name')
                            ->badge()
                            ->color('primary')
                            ->default('Tous niveaux')
                            ->sortable(),

                        TextColumn::make('author')
                            ->color('gray')
                            ->prefix('Par ')
                            ->placeholder('Incubateur')
                            ->alignEnd(),
                    ]),
                ])->space(3),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Format')
                    ->options([
                        'DOCUMENT' => 'Document (PDF, Word...)',
                        'VIDEO' => 'Vidéo / Formation',
                        'LIEN_EXTERNE' => 'Lien / Outil web',
                    ]),

                SelectFilter::make('category')
                    ->label('Thématique')
                    ->options(function () {
                        $dbCategories = ResourceModel::query()
                            ->whereNotNull('category')
                            ->distinct()
                            ->pluck('category', 'category')
                            ->toArray();

                        $defaults = [
                            'Entrepreneuriat' => 'Entrepreneuriat',
                            'Finance' => 'Finance & Levée',
                            'Juridique' => 'Juridique & Fiscal',
                            'Marketing' => 'Marketing & Vente',
                            'Technique' => 'Technique & Produit',
                            'Pitch' => 'Pitch & Communication',
                        ];

                        return array_merge($defaults, $dbCategories);
                    })
                    ->searchable(),

                SelectFilter::make('level_id')
                    ->label('Niveau recommandé')
                    ->relationship('level', 'name')
                    ->preload(),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->recordActions([
                Action::make('open')
                    ->label('Consulter')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('primary')
                    ->button()
                    ->size('sm')
                    ->url(fn ($record) => $record->url ?: ($record->file_path ? asset('storage/'.$record->file_path) : null))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->url) || filled($record->file_path)),

                ViewAction::make()
                    ->label('Détails')
                    ->slideOver()
                    ->button()
                    ->size('sm')
                    ->color('gray'),
            ])
            ->recordActionsAlignment('end')
            ->defaultSort('created_at', 'desc');
    }
}
