<?php

namespace App\Filament\Sae\Pages\Tenancy;

use App\Models\Sae;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RegisterSae extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Enregistrer une Structure d\'Accompagnement (SAE)';
    }

    public function mount(): void
    {
        // Règle stricte : Un utilisateur ne peut créer qu'une seule structure SAE
        if (! auth()->user()->canCreateTenants()) {
            $existingSae = auth()->user()->ownedSae ?? auth()->user()->saes()->first();
            if ($existingSae) {
                $this->redirect(filament()->getPanel('sae')->getUrl($existingSae));

                return;
            }
            abort(403, 'Vous possédez déjà une structure d\'accompagnement.');
        }

        parent::mount();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom de la SAE / Incubateur')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                TextInput::make('slug')
                    ->label('Identifiant unique (Slug URL)')
                    ->required()
                    ->unique(Sae::class, 'slug')
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email de contact')
                    ->email()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel()
                    ->maxLength(50),

                Textarea::make('address')
                    ->label('Adresse / Ville / Pays')
                    ->rows(2),

                Textarea::make('description')
                    ->label('Description & Mission')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    protected function handleRegistration(array $data): Sae
    {
        $user = auth()->user();

        if (! $user->canCreateTenants()) {
            abort(403, 'Vous ne pouvez créer qu\'une seule structure d\'accompagnement.');
        }

        $data['owner_id'] = $user->id;
        $data['status'] = 'ACTIVE';

        $sae = Sae::create($data);

        // Rattachement de l'utilisateur comme RESPONSABLE de la SAE
        $user->saes()->attach($sae->id, [
            'role' => 'RESPONSABLE',
            'status' => 'ACTIVE',
        ]);

        return $sae;
    }
}
