<?php

namespace App\Filament\Sae\Resources\TimelineEvents;

use App\Filament\Sae\Resources\TimelineEvents\Pages\CreateTimelineEvent;
use App\Filament\Sae\Resources\TimelineEvents\Pages\EditTimelineEvent;
use App\Filament\Sae\Resources\TimelineEvents\Pages\ListTimelineEvents;
use App\Filament\Sae\Resources\TimelineEvents\Schemas\TimelineEventForm;
use App\Filament\Sae\Resources\TimelineEvents\Tables\TimelineEventsTable;
use App\Models\TimelineEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TimelineEventResource extends Resource
{
    protected static ?string $model = TimelineEvent::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Portefeuille';

    protected static ?string $navigationLabel = 'Historique des parcours';

    protected static ?string $modelLabel = 'Événement de parcours';

    protected static ?string $pluralModelLabel = 'Historique des parcours';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return TimelineEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimelineEventsTable::configure($table);
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
            'index' => ListTimelineEvents::route('/'),
            'create' => CreateTimelineEvent::route('/create'),
            'edit' => EditTimelineEvent::route('/{record}/edit'),
        ];
    }
}


