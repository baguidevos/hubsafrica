<?php

namespace App\Filament\Sae\Resources\Partners\Schemas;

use App\Enums\PartnerType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité institutionnelle')
                    ->description('Renseignez les informations générales de l\'organisation partenaire.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nom du partenaire / de l\'institution')
                                ->placeholder('Ex: AFD, GIZ, Orange Digital Center...')
                                ->required(),

                            Select::make('type')
                                ->label('Typologie de partenaire')
                                ->options(PartnerType::class)
                                ->default(PartnerType::BAILLEUR)
                                ->required(),

                            TextInput::make('website')
                                ->label('Site web officiel')
                                ->url()
                                ->placeholder('https://www.organisation.org')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Point de contact & Référent')
                    ->description('Personne en charge de la relation au sein de l\'institution.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('contact_name')
                                ->label('Nom du référent')
                                ->placeholder('Ex: Dr. Fatoumata Diallo'),

                            TextInput::make('contact_email')
                                ->label('Adresse email')
                                ->email()
                                ->placeholder('f.diallo@partenaire.org'),

                            TextInput::make('contact_phone')
                                ->label('Téléphone')
                                ->tel()
                                ->placeholder('+221 77 000 00 00'),
                        ]),

                        Textarea::make('notes')
                            ->label('Notes internes & conventions de partenariat')
                            ->placeholder('Historique des subventions, axes prioritaires d\'intervention, date de fin d\'accord...')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Visible uniquement par les administrateurs et responsables de la structure.'),
                    ]),
            ]);
    }
}
