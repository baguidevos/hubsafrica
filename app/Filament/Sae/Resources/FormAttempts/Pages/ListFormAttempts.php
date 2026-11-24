<?php

namespace App\Filament\Sae\Resources\FormAttempts\Pages;

use App\Filament\Sae\Resources\FormAttempts\FormAttemptResource;
use App\Models\FormAttempt;
use App\Services\FormAttemptTracker;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListFormAttempts extends ListRecords
{
    protected static string $resource = FormAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportFormAttempts')
                ->label('Exporter en Excel / CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(fn (): StreamedResponse => $this->exportCsv()),
        ];
    }

    protected function exportCsv(): StreamedResponse
    {
        $tenant = Filament::getTenant();
        $slug = Str::slug((string) ($tenant?->slug ?: $tenant?->name ?: 'sae'));
        $filename = "formulaires-non-finalises-{$slug}-".now()->format('Ymd-His').'.csv';
        $tracker = app(FormAttemptTracker::class);

        $query = $this->getFilteredTableQuery()
            ->with(['user', 'entrepreneur', 'form', 'cohort'])
            ->latest('last_activity_at');

        return response()->streamDownload(function () use ($query, $tracker): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $tracker->exportHeaders(), ';');

            /** @var FormAttempt $attempt */
            foreach ($query->cursor() as $attempt) {
                fputcsv($handle, $tracker->formatExportRow($attempt), ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
