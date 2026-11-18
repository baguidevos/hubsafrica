<?php

namespace App\Filament\Admin\Resources\Entrepreneurs\Pages;

use App\Filament\Admin\Resources\Entrepreneurs\EntrepreneurResource;
use App\Filament\Concerns\HasDynamicRecordTitle;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEntrepreneur extends ViewRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = EntrepreneurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('access_portal')
                ->label('Accéder au portail entrepreneur')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('success')
                ->tooltip('Se connecter et ouvrir le portail de cet entrepreneur')
                ->url(fn ($record) => route('admin.impersonate.entrepreneur', $record)),
            EditAction::make(),
        ];
    }
}
