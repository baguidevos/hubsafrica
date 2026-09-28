<?php

namespace App\Filament\Sae\Resources\AuditLogs;

use App\Filament\Sae\Resources\AuditLogs\Pages\CreateAuditLog;
use App\Filament\Sae\Resources\AuditLogs\Pages\EditAuditLog;
use App\Filament\Sae\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Filament\Sae\Resources\AuditLogs\Schemas\AuditLogForm;
use App\Filament\Sae\Resources\AuditLogs\Tables\AuditLogsTable;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?string $navigationLabel = 'Journal d\'audit';

    protected static ?string $modelLabel = 'Journal d\'audit';

    protected static ?string $pluralModelLabel = 'Journal d\'audit';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AuditLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogs::route('/'),
            'create' => CreateAuditLog::route('/create'),
            'edit' => EditAuditLog::route('/{record}/edit'),
        ];
    }
}
