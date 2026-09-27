<?php

namespace App\Filament\Entrepreneur\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ProfilePage extends Page
{
    protected static string | \UnitEnum | null $navigationGroup = 'Mon espace';

    protected static ?string $navigationLabel = 'Mon profil';

    protected static ?string $title = 'Mon profil entrepreneur';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.entrepreneur.pages.profile-page';

    public ?string $firstName = '';
    public ?string $lastName = '';
    public ?string $email = '';
    public ?string $phone = '';
    public ?string $gender = '';
    public ?string $birthDate = '';
    public ?string $country = '';
    public ?string $territory = '';
    public ?string $educationLevel = '';
    public ?string $profession = '';
    public ?string $professionalStatus = '';

    public function mount(): void
    {
        $entrepreneur = auth()->user()?->entrepreneurProfile;
        if ($entrepreneur) {
            $this->firstName = $entrepreneur->first_name;
            $this->lastName = $entrepreneur->last_name;
            $this->email = $entrepreneur->email ?? auth()->user()?->email;
            $this->phone = $entrepreneur->phone;
            $this->gender = $entrepreneur->gender instanceof \App\Enums\Gender ? $entrepreneur->gender->value : ($entrepreneur->gender ? (string) $entrepreneur->gender : null);
            $this->birthDate = $entrepreneur->birth_date?->format('Y-m-d');
            $this->country = $entrepreneur->country;
            $this->territory = $entrepreneur->territory;
            $this->educationLevel = $entrepreneur->education_level;
            $this->profession = $entrepreneur->profession;
            $this->professionalStatus = $entrepreneur->professional_status;
        }
    }

    public function save(): void
    {
        $this->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'gender' => 'nullable|string|in:M,F,AUTRE',
            'birthDate' => 'nullable|date',
            'country' => 'nullable|string|max:100',
            'territory' => 'nullable|string|max:100',
            'educationLevel' => 'nullable|string|max:100',
            'profession' => 'nullable|string|max:100',
            'professionalStatus' => 'nullable|string|max:100',
        ]);

        $entrepreneur = auth()->user()?->entrepreneurProfile;
        if ($entrepreneur) {
            $entrepreneur->update([
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'email' => $this->email,
                'phone' => $this->phone,
                'gender' => $this->gender,
                'birth_date' => $this->birthDate ?: null,
                'country' => $this->country,
                'territory' => $this->territory,
                'education_level' => $this->educationLevel,
                'profession' => $this->profession,
                'professional_status' => $this->professionalStatus,
            ]);

            Notification::make()
                ->title('Profil mis à jour avec succès')
                ->success()
                ->send();
        }
    }
}


