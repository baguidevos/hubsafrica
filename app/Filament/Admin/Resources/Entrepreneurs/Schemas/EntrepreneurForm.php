<?php

namespace App\Filament\Admin\Resources\Entrepreneurs\Schemas;

use App\Enums\EntrepreneurStatus;
use App\Enums\Gender;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EntrepreneurForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->deferLoading()
            ->components([
                Section::make('Rattachement Institutionnel')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('sae_id')
                                ->label('Structure SAE partenaire')
                                ->relationship('sae', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('user_id')
                                ->label('Compte utilisateur (Connexion)')
                                ->relationship('user', 'email')
                                ->searchable()
                                ->preload()
                                ->helperText('Compte utilisateur utilisé pour s\'authentifier sur le portail entrepreneur.'),
                        ]),
                    ]),

                Section::make('État civil & Coordonnées')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('first_name')
                                ->label('Prénom')
                                ->placeholder('Ex: Ousmane')
                                ->required(),

                            TextInput::make('last_name')
                                ->label('Nom de famille')
                                ->placeholder('Ex: Fall')
                                ->required(),

                            TextInput::make('email')
                                ->label('Adresse email')
                                ->email()
                                ->placeholder('ousmane.fall@example.com')
                                ->required(),

                            TextInput::make('phone')
                                ->label('Téléphone')
                                ->tel()
                                ->placeholder('+221 77 123 45 67')
                                ->required(),

                            Select::make('gender')
                                ->label('Genre')
                                ->options(Gender::class)
                                ->required(),

                            DatePicker::make('birth_date')
                                ->label('Date de naissance'),

                            FileUpload::make('photo')
                                ->label('Photo de profil')
                                ->image()
                                ->disk('public')
                                ->directory('entrepreneurs/photos')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Profil sociodémographique & Territoire')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('country')
                                ->label('Pays de résidence')
                                ->default('Sénégal'),

                            TextInput::make('territory')
                                ->label('Région / Territoire')
                                ->placeholder('Ex: Dakar, Abidjan, Bamako'),

                            Select::make('education_level')
                                ->label('Niveau d\'études')
                                ->options([
                                    'Sans diplôme' => 'Sans diplôme',
                                    'Primaire' => 'Primaire',
                                    'Secondaire / BFEM' => 'Secondaire / BFEM',
                                    'Bac' => 'Baccalauréat',
                                    'Bac+2' => 'Bac+2 (BTS, DUT)',
                                    'Licence' => 'Licence / Bac+3',
                                    'Master' => 'Master / Ingénieur / Bac+5',
                                    'Doctorat' => 'Doctorat / PhD',
                                ]),

                            TextInput::make('profession')
                                ->label('Métier / Profession')
                                ->placeholder('Ex: Ingénieur logiciel, Agronome'),

                            Select::make('professional_status')
                                ->label('Situation professionnelle')
                                ->options([
                                    'Demandeur d\'emploi' => 'Demandeur d\'emploi',
                                    'Salarié' => 'Salarié',
                                    'Indépendant' => 'Indépendant / Auto-entrepreneur',
                                    'Étudiant' => 'Étudiant',
                                    'Autre' => 'Autre',
                                ]),

                            Select::make('status')
                                ->label('Statut du dossier')
                                ->options(EntrepreneurStatus::class)
                                ->default(EntrepreneurStatus::ACTIVE)
                                ->required(),
                        ]),
                    ]),

                Section::make('Observations & Historique')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes internes Hubs Africa')
                            ->rows(3)
                            ->placeholder('Observations sur le profil ou l\'accompagnement...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
