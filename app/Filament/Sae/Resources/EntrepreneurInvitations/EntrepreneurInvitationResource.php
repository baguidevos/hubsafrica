<?php

namespace App\Filament\Sae\Resources\EntrepreneurInvitations;

use App\Filament\Sae\Clusters\PortefeuilleCluster;
use App\Filament\Sae\Resources\EntrepreneurInvitations\Pages\CreateEntrepreneurInvitation;
use App\Filament\Sae\Resources\EntrepreneurInvitations\Pages\EditEntrepreneurInvitation;
use App\Filament\Sae\Resources\EntrepreneurInvitations\Pages\ListEntrepreneurInvitations;
use App\Filament\Sae\Resources\EntrepreneurInvitations\Schemas\EntrepreneurInvitationForm;
use App\Filament\Sae\Resources\EntrepreneurInvitations\Tables\EntrepreneurInvitationsTable;
use App\Models\EntrepreneurInvitation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EntrepreneurInvitationResource extends Resource
{
    protected static ?string $model = EntrepreneurInvitation::class;

    protected static ?string $cluster = PortefeuilleCluster::class;

    protected static ?string $navigationLabel = 'Invitations entrepreneurs';

    protected static ?string $modelLabel = 'Invitation';

    protected static ?string $pluralModelLabel = 'Invitations d\'entrepreneurs';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope-open';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return EntrepreneurInvitationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EntrepreneurInvitationsTable::configure($table);
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
            'index' => ListEntrepreneurInvitations::route('/'),
            'create' => CreateEntrepreneurInvitation::route('/create'),
            'edit' => EditEntrepreneurInvitation::route('/{record}/edit'),
        ];
    }
}
