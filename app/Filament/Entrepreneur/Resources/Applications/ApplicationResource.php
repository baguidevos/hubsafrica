<?php

namespace App\Filament\Entrepreneur\Resources\Applications;

use App\Filament\Entrepreneur\Resources\Applications\Pages\ListApplications;
use App\Filament\Sae\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Sae\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Mon espace';

    protected static ?string $navigationLabel = 'Mes candidatures';

    protected static ?string $modelLabel = 'Candidature';

    protected static ?string $pluralModelLabel = 'Mes candidatures';

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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $entrepreneur = auth()->user()?->entrepreneurProfile;
        if ($entrepreneur) {
            $query->where('entrepreneur_id', $entrepreneur->id);
        }
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
        ];
    }
}


