<?php

namespace App\Filament\Admin\Resources\Entrepreneurs\Pages;

use App\Enums\EntrepreneurStatus;
use App\Filament\Admin\Resources\Entrepreneurs\EntrepreneurResource;
use App\Models\Entrepreneur;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListEntrepreneurs extends ListRecords
{
    protected static string $resource = EntrepreneurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvel entrepreneur'),
            Action::make('open_portal')
                ->label('Ouvrir le portail (Vue générale)')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(url('/portal'))
                ->openUrlInNewTab(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous')
                ->badge(Entrepreneur::count()),
            'active' => Tab::make('Actifs')
                ->modifyQueryUsing(fn ($query) => $query->where('status', EntrepreneurStatus::ACTIVE))
                ->badge(Entrepreneur::where('status', EntrepreneurStatus::ACTIVE)->count()),
            'inactive' => Tab::make('Non actifs')
                ->modifyQueryUsing(fn ($query) => $query->where('status', '!=', EntrepreneurStatus::ACTIVE))
                ->badge(Entrepreneur::where('status', '!=', EntrepreneurStatus::ACTIVE)->count()),
            'with_projects' => Tab::make('Avec projet(s)')
                ->modifyQueryUsing(fn ($query) => $query->has('projects'))
                ->badge(Entrepreneur::has('projects')->count()),
            'without_projects' => Tab::make('Sans projet')
                ->modifyQueryUsing(fn ($query) => $query->doesntHave('projects'))
                ->badge(Entrepreneur::doesntHave('projects')->count()),
        ];
    }
}
