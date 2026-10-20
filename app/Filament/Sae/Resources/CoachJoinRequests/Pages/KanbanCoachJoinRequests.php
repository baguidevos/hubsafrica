<?php

namespace App\Filament\Sae\Resources\CoachJoinRequests\Pages;

use App\Enums\CoachJoinRequestStatus;
use App\Filament\Sae\Resources\CoachJoinRequests\CoachJoinRequestResource;
use App\Models\CoachJoinRequest;
use EduardoRibeiroDev\FilamentKanban\Board;
use EduardoRibeiroDev\FilamentKanban\BoardColumn;
use EduardoRibeiroDev\FilamentKanban\Enums\CardView;
use EduardoRibeiroDev\FilamentKanban\Resources\Pages\BoardPage;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;

class KanbanCoachJoinRequests extends BoardPage
{
    protected static string $resource = CoachJoinRequestResource::class;

    protected static ?string $title = 'Recrutement des Coachs';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('list')
                ->label('Vue Liste')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(static::getResource()::getUrl('index')),
        ];
    }

    public function board(Board $board): Board
    {
        return $board
            ->query(fn () => CoachJoinRequest::query())
            ->statusColumn('status')
            ->columns([
                BoardColumn::make(CoachJoinRequestStatus::PENDING->value)
                    ->label(CoachJoinRequestStatus::PENDING->getLabel())
                    ->color(CoachJoinRequestStatus::PENDING->getColor())
                    ->icon('heroicon-o-clock'),

                BoardColumn::make(CoachJoinRequestStatus::ACCEPTED->value)
                    ->label(CoachJoinRequestStatus::ACCEPTED->getLabel())
                    ->color(CoachJoinRequestStatus::ACCEPTED->getColor())
                    ->icon('heroicon-o-check-badge'),

                BoardColumn::make(CoachJoinRequestStatus::REJECTED->value)
                    ->label(CoachJoinRequestStatus::REJECTED->getLabel())
                    ->color(CoachJoinRequestStatus::REJECTED->getColor())
                    ->icon('heroicon-o-x-circle'),
            ])
            ->cardView(CardView::Accent)
            ->cardSchema([
                TextEntry::make('name')
                    ->label('Coach candidat')
                    ->weight('bold')
                    ->color('primary')
                    ->icon('heroicon-m-academic-cap'),

                TextEntry::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope')
                    ->color('gray'),

                TextEntry::make('phone')
                    ->label('Téléphone')
                    ->icon('heroicon-m-phone')
                    ->placeholder('Non renseigné')
                    ->color('gray'),

                TextEntry::make('specialties')
                    ->label('Spécialités')
                    ->badge()
                    ->separator(',')
                    ->limitList(3)
                    ->placeholder('Expertise générale'),

                TextEntry::make('created_at')
                    ->label('Candidaté le')
                    ->dateTime('d/m/Y')
                    ->icon('heroicon-m-calendar')
                    ->color('gray'),
            ])
            ->cardActions([
                Action::make('quickAccept')
                    ->label('Accepter')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (CoachJoinRequest $record) => $record->status !== CoachJoinRequestStatus::ACCEPTED)
                    ->requiresConfirmation()
                    ->action(function (CoachJoinRequest $record) {
                        $record->status = CoachJoinRequestStatus::ACCEPTED;
                        $record->processed_at = now();
                        $record->processed_by = auth()->id();
                        $record->save();

                        Notification::make()
                            ->title('Candidature acceptée')
                            ->success()
                            ->send();
                    }),

                Action::make('quickReject')
                    ->label('Refuser')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->visible(fn (CoachJoinRequest $record) => $record->status !== CoachJoinRequestStatus::REJECTED)
                    ->requiresConfirmation()
                    ->action(function (CoachJoinRequest $record) {
                        $record->status = CoachJoinRequestStatus::REJECTED;
                        $record->processed_at = now();
                        $record->processed_by = auth()->id();
                        $record->save();

                        Notification::make()
                            ->title('Candidature refusée')
                            ->danger()
                            ->send();
                    }),
            ])
            ->compactCardActions()
            ->updateStatusUsing(function (CoachJoinRequest $record, string $columnId): void {
                $newStatus = CoachJoinRequestStatus::tryFrom($columnId);
                if ($newStatus) {
                    $record->status = $newStatus;
                    $record->processed_at = now();
                    $record->processed_by = auth()->id();
                    $record->save();
                }
            })
            ->deferFilters(false)
            ->cardsPerPage(30);
    }
}
