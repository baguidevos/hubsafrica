<?php

namespace App\Filament\Sae\Pages\Tenancy;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

class EditSaeProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Profil de la SAE';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom de la structure')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email de contact')
                    ->email(),

                TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel(),

                TextInput::make('website')
                    ->label('Site web institutionnel')
                    ->url()
                    ->placeholder('https://www.incubateur.africa'),

                Textarea::make('address')
                    ->label('Adresse')
                    ->rows(2),

                Textarea::make('description')
                    ->label('Présentation synthétique')
                    ->rows(3)
                    ->columnSpanFull(),

                RichEditor::make('vision')
                    ->label('Vision stratégique')
                    ->helperText('Quelle est la vision à long terme de votre structure ?')
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'undo', 'redo',
                    ])
                    ->columnSpanFull(),

                RichEditor::make('mission')
                    ->label('Mission & Raison d\'être')
                    ->helperText('Quelle est la mission opérationnelle auprès des startups ?')
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'undo', 'redo',
                    ])
                    ->columnSpanFull(),

                RichEditor::make('objectives')
                    ->label('Objectifs prioritaires & Piliers d\'impact')
                    ->helperText('Objectifs qualitatifs et quantitatifs (emplois, levées de fonds, parité, etc.).')
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'undo', 'redo',
                    ])
                    ->columnSpanFull(),

                RichEditor::make('services')
                    ->label('Services & Offre d\'accompagnement')
                    ->helperText('Mentorat, bootcamps, mise en relation investisseurs, fablab, hébergement...')
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'undo', 'redo',
                    ])
                    ->columnSpanFull(),

                TextInput::make('default_admission_threshold')
                    ->label('Seuil d\'admission directe par défaut (/100)')
                    ->helperText('Note minimale requise par défaut pour admettre automatiquement une candidature sur vos programmes.')
                    ->default(75)
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100),

                TextInput::make('default_waitlist_threshold')
                    ->label('Seuil de liste d\'attente par défaut (/100)')
                    ->helperText('Note minimale par défaut pour la liste d\'attente. En-dessous de ce seuil, le dossier est refusé.')
                    ->default(50)
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
            ]);
    }
}
