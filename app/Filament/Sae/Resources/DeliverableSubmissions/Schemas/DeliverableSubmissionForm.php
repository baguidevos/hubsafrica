<?php

namespace App\Filament\Sae\Resources\DeliverableSubmissions\Schemas;

use App\Enums\DeliverableSubmissionStatus;
use App\Models\Entrepreneur;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeliverableSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cadrage du livrable déposé')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('definition_id')
                                ->label('Livrable attendu')
                                ->relationship('definition', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('cohort_id')
                                ->label('Cohorte')
                                ->relationship('cohort', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('project_id')
                                ->label('Projet associé')
                                ->relationship('project', 'name')
                                ->searchable()
                                ->preload()
                                ->helperText('Laisser vide pour les livrables SAEI / Programme transversaux'),

                            Select::make('entrepreneur_id')
                                ->label('Entrepreneur déposant')
                                ->options(function () {
                                    return Entrepreneur::all()->mapWithKeys(function ($e) {
                                        return [$e->id => "{$e->first_name} {$e->last_name}"];
                                    });
                                })
                                ->searchable()
                                ->preload(),

                            TextInput::make('version')
                                ->label('Numéro de version')
                                ->numeric()
                                ->default(1)
                                ->prefix('v')
                                ->required(),
                        ]),
                    ]),

                Section::make('Fichier & Commentaires')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Fichier joint')
                            ->disk('public')
                            ->directory('deliverables')
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull(),

                        TextInput::make('file_name')
                            ->label('Intitulé du document')
                            ->placeholder('Ex: BMC_Ecobat_version_finale.pdf')
                            ->columnSpanFull(),

                        Textarea::make('comment')
                            ->label('Notes de l\'auteur / Commentaire de soumission')
                            ->rows(3)
                            ->placeholder('Précisions éventuelles apportées par l\'entrepreneur...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Statut & Date')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('status')
                                ->label('Statut de la soumission')
                                ->options(DeliverableSubmissionStatus::class)
                                ->default(DeliverableSubmissionStatus::SOUMIS)
                                ->required(),

                            DateTimePicker::make('submitted_at')
                                ->label('Date & heure de dépôt')
                                ->default(now())
                                ->required(),

                            Toggle::make('is_late')
                                ->label('En retard ?')
                                ->helperText('Indique si le livrable a été déposé après la date limite')
                                ->inline(false),
                        ]),
                    ]),
            ]);
    }
}
