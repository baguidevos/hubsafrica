<?php

namespace App\Filament\Sae\Resources\EvaluationForms\RelationManagers;

use App\Services\FormSubmissionsExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Packstub\FormBuilder\Filament\Resources\FormResource\RelationManagers\SubmissionsRelationManager as BaseSubmissionsRelationManager;
use Packstub\FormBuilder\Models\Form;

class SubmissionsRelationManager extends BaseSubmissionsRelationManager
{
    public function table(Table $table): Table
    {
        /** @var Form $form */
        $form = $this->getOwnerRecord();

        return parent::table($table)
            ->headerActions([
                Action::make('export')
                    ->label(__('packstub-form-builder::form-builder.submissions.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn () => FormSubmissionsExportService::download($form, $this->getFilteredTableQuery())),
                Action::make('exportXlsx')
                    ->label(__('packstub-form-builder::form-builder.submissions.export_xlsx'))
                    ->icon('heroicon-o-table-cells')
                    ->color('gray')
                    ->visible(fn (): bool => FormSubmissionsExportService::hasXlsx())
                    ->action(fn () => FormSubmissionsExportService::download($form, $this->getFilteredTableQuery(), 'xlsx')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markRead')
                        ->label(__('packstub-form-builder::form-builder.submissions.mark_read'))
                        ->icon('heroicon-o-envelope-open')
                        ->action(fn (Collection $records) => $records->each->markRead())
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('exportSelected')
                        ->label(__('packstub-form-builder::form-builder.submissions.export'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (Collection $records) => FormSubmissionsExportService::download($form, $records)),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
