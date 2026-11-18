<?php

namespace App\Filament\Admin\Resources\Entrepreneurs\Pages;

use App\Filament\Admin\Resources\Entrepreneurs\EntrepreneurResource;
use App\Filament\Concerns\HasDynamicRecordTitle;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEntrepreneur extends EditRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = EntrepreneurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('access_portal')
                ->label('Accéder au portail')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('success')
                ->url(fn ($record) => route('admin.impersonate.entrepreneur', $record)),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
