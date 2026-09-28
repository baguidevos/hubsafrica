<?php

namespace App\Filament\Sae\Resources\CoachInvitations;

use App\Filament\Sae\Clusters\AccompagnementCluster;
use App\Filament\Sae\Resources\CoachInvitations\Pages\CreateCoachInvitation;
use App\Filament\Sae\Resources\CoachInvitations\Pages\EditCoachInvitation;
use App\Filament\Sae\Resources\CoachInvitations\Pages\ListCoachInvitations;
use App\Filament\Sae\Resources\CoachInvitations\Schemas\CoachInvitationForm;
use App\Filament\Sae\Resources\CoachInvitations\Tables\CoachInvitationsTable;
use App\Models\CoachInvitation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CoachInvitationResource extends Resource
{
    protected static ?string $model = CoachInvitation::class;

    protected static ?string $cluster = AccompagnementCluster::class;

    protected static ?string $navigationLabel = 'Invitations de coachs';

    protected static ?string $modelLabel = 'Invitation';

    protected static ?string $pluralModelLabel = 'Invitations de coachs';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CoachInvitationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoachInvitationsTable::configure($table);
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
            'index' => ListCoachInvitations::route('/'),
            'create' => CreateCoachInvitation::route('/create'),
            'edit' => EditCoachInvitation::route('/{record}/edit'),
        ];
    }
}
