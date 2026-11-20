<?php

namespace App\Filament\Admin\Resources\Entrepreneurs\Tables;

use App\Enums\EntrepreneurStatus;
use App\Enums\Gender;
use App\Filament\Admin\Resources\Entrepreneurs\EntrepreneurResource;
use App\Models\Entrepreneur;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EntrepreneursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn (Entrepreneur $record) => 'https://ui-avatars.com/api/?name='.urlencode($record->full_name).'&color=FFFFFF&background=0F766E'),

                TextColumn::make('full_name')
                    ->label('Entrepreneur')
                    ->weight('bold')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name', 'first_name'])
                    ->description(fn (Entrepreneur $record) => $record->profession ?: ($record->territory ?: null)),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('sae.name')
                    ->label('Structure SAE')
                    ->badge()
                    ->color('slate')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Aucune structure'),

                TextColumn::make('projects_count')
                    ->counts('projects')
                    ->label('Projets remplis')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => '0 projet',
                        1 => '1 projet rempli',
                        default => "{$state} projets remplis",
                    })
                    ->sortable(),

                TextColumn::make('projects.name')
                    ->label('Nom des projets')
                    ->badge()
                    ->color('gray')
                    ->separator(', ')
                    ->limit(30)
                    ->placeholder('Aucun projet renseigné')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('gender')
                    ->label('Genre')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cohorts.name')
                    ->label('Cohorte(s)')
                    ->badge()
                    ->color('info')
                    ->placeholder('Hors cohorte')
                    ->separator(', ')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('activity')
                    ->label('Activité (Actif / Non actif)')
                    ->options([
                        'active' => 'Actifs uniquement',
                        'inactive' => 'Non actifs (Abandonnés, Suspendus, Candidats)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (($data['value'] ?? null) === 'active') {
                            return $query->where('status', EntrepreneurStatus::ACTIVE);
                        }

                        if (($data['value'] ?? null) === 'inactive') {
                            return $query->where('status', '!=', EntrepreneurStatus::ACTIVE);
                        }

                        return $query;
                    }),

                SelectFilter::make('has_projects')
                    ->label('Projets renseignés')
                    ->options([
                        'with_projects' => 'Ayant rempli au moins un projet',
                        'without_projects' => 'Sans aucun projet',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (($data['value'] ?? null) === 'with_projects') {
                            return $query->has('projects');
                        }

                        if (($data['value'] ?? null) === 'without_projects') {
                            return $query->doesntHave('projects');
                        }

                        return $query;
                    }),

                SelectFilter::make('sae_id')
                    ->label('Structure SAE')
                    ->relationship('sae', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Statut détaillé')
                    ->options(EntrepreneurStatus::class),

                SelectFilter::make('has_applications')
                    ->label('Candidatures déposées')
                    ->options([
                        'with_applications' => 'Avec candidature',
                        'without_applications' => 'Sans candidature',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (($data['value'] ?? null) === 'with_applications') {
                            return $query->has('applications');
                        }

                        if (($data['value'] ?? null) === 'without_applications') {
                            return $query->doesntHave('applications');
                        }

                        return $query;
                    }),

                SelectFilter::make('gender')
                    ->label('Genre')
                    ->options(Gender::class),
            ])
            ->recordActions([
                Action::make('access_portal')
                    ->label('Accéder au portail')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('success')
                    ->tooltip('Ouvrir l\'espace portail entrepreneur de cet utilisateur')
                    ->url(fn (Entrepreneur $record): string => route('admin.impersonate.entrepreneur', $record)),

                ViewAction::make()
                    ->url(fn (Entrepreneur $record): string => EntrepreneurResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn (Entrepreneur $record): string => EntrepreneurResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ])
            ->recordUrl(fn (Entrepreneur $record): string => EntrepreneurResource::getUrl('view', ['record' => $record]))
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
