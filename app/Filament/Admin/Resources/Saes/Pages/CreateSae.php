<?php

namespace App\Filament\Admin\Resources\Saes\Pages;

use App\Filament\Admin\Resources\Saes\SaeResource;
use App\Services\SaeOnboardingService;
use Filament\Resources\Pages\CreateRecord;

class CreateSae extends CreateRecord
{
    protected static string $resource = SaeResource::class;

    protected function afterCreate(): void
    {
        $owner = $this->record->owner;
        app(SaeOnboardingService::class)->initializeSaeDefaults($this->record, $owner);
    }
}
