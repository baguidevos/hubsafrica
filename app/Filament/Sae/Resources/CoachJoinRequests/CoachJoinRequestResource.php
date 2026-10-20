<?php

namespace App\Filament\Sae\Resources\CoachJoinRequests;

use App\Filament\Sae\Clusters\AccompagnementCluster;
use App\Filament\Sae\Resources\CoachJoinRequests\Pages\KanbanCoachJoinRequests;
use App\Filament\Sae\Resources\CoachJoinRequests\Pages\ListCoachJoinRequests;
use App\Filament\Sae\Resources\CoachJoinRequests\Tables\CoachJoinRequestsTable;
use App\Models\CoachJoinRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class CoachJoinRequestResource extends Resource
{
    protected static ?string $model = CoachJoinRequest::class;

    protected static ?string $cluster = AccompagnementCluster::class;

    protected static ?string $navigationLabel = 'Candidatures de coachs';

    protected static ?string $modelLabel = 'Candidature Coach';

    protected static ?string $pluralModelLabel = 'Candidatures de coachs';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'PENDING')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return CoachJoinRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoachJoinRequests::route('/'),
            'kanban' => KanbanCoachJoinRequests::route('/kanban'),
        ];
    }
}
