<?php

namespace App\Filament\Sae\Resources\FundingRecords\Schemas;

use App\Enums\FundingStatus;
use App\Enums\FundingType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FundingRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cadrage du financement')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('project_id')
                                ->label('Projet bénéficiaire')
                                ->relationship('project', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),

                            Select::make('type')
                                ->label('Type d\'instrument')
                                ->options(FundingType::class)
                                ->required(),

                            Select::make('status')
                                ->label('Statut du financement')
                                ->options(FundingStatus::class)
                                ->default(FundingStatus::DEMANDE)
                                ->required(),

                            TextInput::make('source')
                                ->label('Organisme / Partenaire financier')
                                ->placeholder('Ex: Bpifrance, FONSIS, DER/FJ, Banque...')
                                ->maxLength(255),

                            DatePicker::make('date')
                                ->label('Date de notification / décaissement')
                                ->default(now()),

                            Toggle::make('verified')
                                ->label('Financement vérifié & audité')
                                ->helperText('Pièces justificatives contrôlées par la SAE')
                                ->inline(false),
                        ]),
                    ]),

                Section::make('Montants financiers')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('amount_sought')
                                ->label('Montant sollicité')
                                ->numeric()
                                ->prefix('FCFA')
                                ->placeholder('0'),

                            TextInput::make('amount_obtained')
                                ->label('Montant accordé / obtenu')
                                ->numeric()
                                ->prefix('FCFA')
                                ->placeholder('0'),

                            TextInput::make('amount_invested')
                                ->label('Montant effectivement investi')
                                ->numeric()
                                ->prefix('FCFA')
                                ->placeholder('0'),
                        ]),
                    ]),

                Section::make('Justificatifs & Observations')
                    ->schema([
                        Textarea::make('justification')
                            ->label('Justificatif / Modalités de déblocage')
                            ->rows(3)
                            ->placeholder('Modalités des tranches, contreparties, garanties requises...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
