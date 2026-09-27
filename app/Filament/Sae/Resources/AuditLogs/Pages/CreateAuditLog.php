<?php

namespace App\Filament\Sae\Resources\AuditLogs\Pages;

use App\Filament\Sae\Resources\AuditLogs\AuditLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAuditLog extends CreateRecord
{
    protected static string $resource = AuditLogResource::class;
}


