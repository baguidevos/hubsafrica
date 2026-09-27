<?php

namespace App\Filament\Sae\Resources\Applications;

use App\Filament\Sae\Resources\Applications\Pages\CreateApplication;
use App\Filament\Sae\Resources\Applications\Pages\EditApplication;
use App\Filament\Sae\Resources\Applications\Pages\ListApplications;
use App\Filament\Sae\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Sae\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Programmes';

    protected static ?string $navigationLabel = 'Candidatures';

    protected static ?string $modelLabel = 'Candidature';

    protected static ?string $pluralModelLabel = 'Candidatures';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
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
            'index' => ListApplications::route('/'),
            'create' => CreateApplication::route('/create'),
            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }
}


