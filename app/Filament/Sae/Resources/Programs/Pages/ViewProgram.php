<?php

namespace App\Filament\Sae\Resources\Programs\Pages;

use App\Filament\Sae\Resources\Programs\ProgramResource;
use App\Models\Program;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewProgram extends ViewRecord
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return false;
    }

    protected function resolveRecord(int|string $key): Model
    {
        /** @var Program $program */
        $program = parent::resolveRecord($key);

        return $program
            ->load(['level', 'sae'])
            ->loadCount(['cohorts', 'applications', 'phases', 'partners']);
    }
}
