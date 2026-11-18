<?php

namespace App\Filament\Sae\Resources\AuditLogs\Pages;

use App\Filament\Concerns\HasDynamicRecordTitle;
use App\Filament\Sae\Resources\AuditLogs\AuditLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAuditLog extends EditRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
