<?php

namespace App\Filament\Coach\Resources\DeliverableSubmissions\Pages;

use App\Filament\Coach\Resources\DeliverableSubmissions\DeliverableSubmissionResource;
use App\Filament\Concerns\HasDynamicRecordTitle;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliverableSubmission extends EditRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = DeliverableSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
