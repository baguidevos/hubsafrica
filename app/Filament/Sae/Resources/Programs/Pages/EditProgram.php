<?php

namespace App\Filament\Sae\Resources\Programs\Pages;

use App\Filament\Sae\Resources\Programs\ProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProgram extends EditRecord
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Informations générales';
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-o-information-circle';
    }
}
