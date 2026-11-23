<?php

namespace App\Filament\Sae\Resources\AuditLogs\Pages;

use App\Filament\Sae\Resources\AuditLogs\AuditLogResource;
use App\Models\AuditLog;
use App\Services\AuditLogger;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportAuditLogs')
                ->label('Télécharger le journal (Excel / CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(fn (): StreamedResponse => $this->exportAuditLogsCsv()),
        ];
    }

    protected function exportAuditLogsCsv(): StreamedResponse
    {
        $tenant = Filament::getTenant();
        $slug = Str::slug((string) ($tenant?->slug ?: $tenant?->name ?: 'sae'));
        $filename = "journal-audit-{$slug}-".now()->format('Ymd-His').'.csv';

        $query = $this->getFilteredTableQuery()
            ->with('user')
            ->latest('created_at');

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, AuditLogger::exportHeaders(), ';');

            /** @var AuditLog $log */
            foreach ($query->cursor() as $log) {
                fputcsv($handle, AuditLogger::formatExportRow($log), ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
