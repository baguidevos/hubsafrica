<?php

namespace App\Filament\Sae\Resources\CoachInvitations\Schemas;

use App\Enums\CoachInvitationStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CoachInvitationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Détails de l\'invitation du Coach')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nom du coach invité')
                                ->placeholder('Ex: Marie Sarr')
                                ->required(),

                            TextInput::make('email')
                                ->label('Adresse e-mail d\'invitation')
                                ->email()
                                ->placeholder('coach@example.com')
                                ->required(),

                            TextInput::make('max_capacity')
                                ->label('Capacité maximale d\'accompagnement')
                                ->numeric()
                                ->default(10)
                                ->suffix('startups')
                                ->required(),

                            Toggle::make('is_external')
                                ->label('Coach externe / Consultant')
                                ->default(true)
                                ->inline(false),
                        ]),

                        TagsInput::make('specialties')
                            ->label('Expertises / Domaines d\'intervention')
                            ->placeholder('Ajouter une compétence')
                            ->columnSpanFull(),
                    ]),

                Section::make('Statut & Validité')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label('Statut de l\'invitation')
                                ->options(CoachInvitationStatus::class)
                                ->default(CoachInvitationStatus::PENDING)
                                ->required(),

                            DateTimePicker::make('expires_at')
                                ->label('Date limite de validité')
                                ->default(fn () => now()->addDays(7))
                                ->required(),
                        ]),
                    ]),
            ]);
    }
}
