<?php

namespace App\Filament\Sae\Resources\EntrepreneurInvitations\Schemas;

use App\Enums\CohortStatus;
use App\Enums\EntrepreneurInvitationStatus;
use App\Models\Cohort;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EntrepreneurInvitationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coordonnées du porteur de projet')
                    ->description('Renseignez les informations de contact de l\'entrepreneur à inviter.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('email')
                                ->label('Adresse e-mail')
                                ->email()
                                ->placeholder('entrepreneur@example.com')
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('first_name')
                                ->label('Prénom')
                                ->placeholder('Ex: Awa'),

                            TextInput::make('last_name')
                                ->label('Nom de famille')
                                ->placeholder('Ex: Diop'),

                            TextInput::make('phone')
                                ->label('Numéro de téléphone')
                                ->tel()
                                ->placeholder('+221 77 000 00 00')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Rattachement & Accompagnement')
                    ->description('Vous pouvez directement intégrer l\'entrepreneur dans une cohorte active de votre structure.')
                    ->schema([
                        Select::make('cohort_id')
                            ->label('Cohorte / Promotion en cours (Optionnel)')
                            ->relationship(
                                name: 'cohort',
                                titleAttribute: 'name',
                                modifyQueryUsing: function ($query) {
                                    $tenant = Filament::getTenant();
                                    if ($tenant) {
                                        $query->where('sae_id', $tenant->id);
                                    }

                                    return $query->whereIn('status', [
                                        CohortStatus::OUVERTE,
                                        CohortStatus::EN_COURS,
                                    ])->with('program');
                                }
                            )
                            ->getOptionLabelFromRecordUsing(fn (Cohort $record) => "{$record->name}".($record->program ? " ({$record->program->name})" : ''))
                            ->searchable()
                            ->preload()
                            ->helperText('Si sélectionnée, l\'entrepreneur sera automatiquement admis et inscrit dans cette cohorte dès l\'acceptation de son invitation.'),

                        TextInput::make('project_name')
                            ->label('Nom du projet ou de la startup (Optionnel)')
                            ->placeholder('Ex: PayTech Solutions')
                            ->helperText('Permet de pré-remplir la fiche projet de l\'entrepreneur lors de son inscription.'),

                        Textarea::make('message')
                            ->label('Message personnalisé d\'accueil')
                            ->placeholder('Ex: Bonjour, nous sommes ravis de vous compter parmi nous pour cette nouvelle phase d\'accompagnement...')
                            ->rows(3)
                            ->helperText('Ce message figurera dans l\'email d\'invitation transmis à l\'entrepreneur.'),
                    ]),

                Section::make('Statut & Validité')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label('Statut de l\'invitation')
                                ->options(EntrepreneurInvitationStatus::class)
                                ->default(EntrepreneurInvitationStatus::PENDING)
                                ->required(),

                            DateTimePicker::make('expires_at')
                                ->label('Date d\'expiration')
                                ->default(fn () => now()->addDays(14))
                                ->required(),
                        ]),
                    ]),
            ]);
    }
}
