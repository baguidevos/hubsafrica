<?php

namespace App\Filament\Sae\Resources\Programs\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PartnersRelationManager extends RelationManager
{
    protected static string $relationship = 'partners';

    protected static ?string $title = 'Partenaires & Bailleurs associés';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-banknotes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('role')
                    ->label('Rôle dans ce programme')
                    ->placeholder('Ex: Bailleur Principal, Sponsor Technique, Fournisseur Cloud')
                    ->required(),

                TextInput::make('contribution_amount')
                    ->label('Montant de la contribution financière')
                    ->numeric()
                    ->suffix('FCFA')
                    ->helperText('Financement octroyé ou valeur de la dotation en nature'),

                TextInput::make('currency')
                    ->label('Devise')
                    ->default('XOF')
                    ->maxLength(3)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Partenaire / Institution')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Typologie')
                    ->badge(),

                TextColumn::make('role')
                    ->label('Rôle / Implication')
                    ->badge()
                    ->color('primary')
                    ->default('Partenaire'),

                TextColumn::make('contribution_amount')
                    ->label('Contribution')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: ' ')
                    ->suffix(' FCFA')
                    ->alignRight()
                    ->weight('bold')
                    ->color('success')
                    ->default('0 FCFA'),

                TextColumn::make('contact_name')
                    ->label('Référent')
                    ->description(fn ($record) => $record->contact_email)
                    ->default('—'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Associer un partenaire')
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),

                        TextInput::make('role')
                            ->label('Rôle dans ce programme')
                            ->placeholder('Ex: Bailleur Principal, Partenaire Technique')
                            ->required(),

                        TextInput::make('contribution_amount')
                            ->label('Contribution financière')
                            ->numeric()
                            ->suffix('FCFA'),

                        TextInput::make('currency')
                            ->label('Devise')
                            ->default('XOF')
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sae_id'] = $this->getOwnerRecord()->sae_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DetachAction::make(),
            ]);
    }
}
