<?php

namespace App\Filament\Admin\Resources\SaeTypes\Schemas;

use App\Models\SaeType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SaeTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom du type')
                    ->placeholder('Ex: Couveuse d\'entreprises, Espace de coworking...')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, $operation) {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),

                TextInput::make('slug')
                    ->label('Slug unique')
                    ->required()
                    ->maxLength(255)
                    ->unique(SaeType::class, 'slug', ignoreRecord: true),

                Select::make('color')
                    ->label('Couleur du badge')
                    ->options([
                        'primary' => 'Teal (Principal)',
                        'indigo' => 'Indigo',
                        'success' => 'Vert (Succès)',
                        'warning' => 'Ambre (Avertissement)',
                        'danger' => 'Rouge',
                        'info' => 'Bleu clair (Info)',
                        'purple' => 'Pourpre',
                        'teal' => 'Teal / Émeraude',
                        'amber' => 'Ambre',
                        'rose' => 'Rose',
                        'gray' => 'Gris neutre',
                    ])
                    ->default('primary'),

                TextInput::make('icon')
                    ->label('Icône Heroicon')
                    ->placeholder('heroicon-o-building-office-2')
                    ->default('heroicon-o-building-office-2'),

                Toggle::make('is_active')
                    ->label('Type actif (disponible pour les SAE)')
                    ->default(true),

                Textarea::make('description')
                    ->label('Description / Rôle du type')
                    ->placeholder('Description du type de SAE, de sa vocation et de ses spécificités...')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
