<?php

namespace App\Filament\Sae\Resources\Courses\Schemas;

use App\Models\Level;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations du cours')
                    ->description('Définissez le titre, le palier d\'accès et la présentation du parcours.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre du cours')
                                    ->placeholder('Ex: Maîtriser son Business Model Canvas')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Select::make('level_id')
                                    ->label('Palier d\'accès (Optionnel)')
                                    ->options(function () {
                                        $tenant = Filament::getTenant();

                                        return Level::query()
                                            ->when($tenant, fn ($q) => $q->where('sae_id', $tenant->id))
                                            ->orderBy('order')
                                            ->pluck('name', 'id');
                                    })
                                    ->placeholder('Tous les paliers (Accès libre SAE)')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Si sélectionné, seuls les entrepreneurs dont le projet est à ce palier pourront suivre les leçons (hors aperçus gratuits).'),

                                TextInput::make('sort_order')
                                    ->label('Ordre d\'affichage')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                FileUpload::make('thumbnail')
                                    ->label('Vignette du cours (Optionnel)')
                                    ->image()
                                    ->disk('public')
                                    ->directory('courses/thumbnails')
                                    ->columnSpanFull(),

                                Toggle::make('is_published')
                                    ->label('Publié (visible sur le portail entrepreneur)')
                                    ->default(false)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
