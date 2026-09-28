<?php

namespace App\Filament\Sae\Resources\Partners;

use App\Filament\Sae\Clusters\ProgrammesCluster;
use App\Filament\Sae\Resources\Partners\Pages\CreatePartner;
use App\Filament\Sae\Resources\Partners\Pages\EditPartner;
use App\Filament\Sae\Resources\Partners\Pages\ListPartners;
use App\Filament\Sae\Resources\Partners\Schemas\PartnerForm;
use App\Filament\Sae\Resources\Partners\Tables\PartnersTable;
use App\Models\Partner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static ?string $cluster = ProgrammesCluster::class;

    protected static ?string $navigationLabel = 'Partenaires & Bailleurs';

    protected static ?string $modelLabel = 'Partenaire';

    protected static ?string $pluralModelLabel = 'Partenaires & Bailleurs';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return PartnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnersTable::configure($table);
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
            'index' => ListPartners::route('/'),
            'create' => CreatePartner::route('/create'),
            'edit' => EditPartner::route('/{record}/edit'),
        ];
    }
}
