<?php

namespace App\Filament\Sae\Resources\Opportunities\Tables;

use App\Models\Opportunity;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OpportunitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Visuel')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => $record->sae?->logo_url)
                    ->toggleable(),

                TextColumn::make('title')
                    ->label('Opportunité')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Opportunity $record): string => $record->sectors ? implode(' • ', $record->sectors) : 'Général'),

                TextColumn::make('status')
                    ->label('État')
                    ->badge()
                    ->getStateUsing(fn (Opportunity $record): string => $record->isClosed() ? 'Clôturé' : 'Ouvert')
                    ->color(fn (Opportunity $record): string => $record->isClosed() ? 'danger' : 'success')
                    ->icon(fn (Opportunity $record): string => $record->isClosed() ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle'),

                TextColumn::make('target_zones')
                    ->label('Zones')
                    ->badge()
                    ->color('info')
                    ->separator(',')
                    ->limitList(2)
                    ->default('Panafricain'),

                TextColumn::make('deadline')
                    ->label('Date limite')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (Opportunity $record): string => $record->getDeadlineStatusLabel())
                    ->color(fn (Opportunity $record): string => $record->isClosed() ? 'danger' : 'gray'),

                IconColumn::make('is_active')
                    ->label('Visible Front')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Publié le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('open')
                    ->label('Candidatures ouvertes uniquement')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)->where(fn ($q) => $q->whereNull('deadline')->orWhere('deadline', '>=', now()))),

                Filter::make('closed')
                    ->label('Clôturées uniquement')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', false)->orWhere(fn ($q) => $q->whereNotNull('deadline')->where('deadline', '<', now()))),

                TernaryFilter::make('is_active')
                    ->label('Statut de publication')
                    ->placeholder('Toutes les opportunités')
                    ->trueLabel('Publiées uniquement')
                    ->falseLabel('Brouillons / Masquées'),
            ])
            ->recordActions([
                Action::make('shareModal')
                    ->label('Partager')
                    ->icon('heroicon-o-share')
                    ->color('amber')
                    ->tooltip('Partager cette opportunité sur les réseaux sociaux ou copier le lien direct')
                    ->modalHeading(fn (Opportunity $record): string => "Partager : {$record->title}")
                    ->modalDescription('Diffusez cet appel à projets auprès de votre écosystème avec un lien public sécurisé.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->form([
                        TextInput::make('public_share_url')
                            ->label('Lien direct officiel')
                            ->default(fn (Opportunity $record): string => $record->share_url)
                            ->readOnly()
                            ->helperText('Ce lien amène directement sur la page de l\'opportunité sur Hubs Africa sans révéler la base de données.'),
                    ])
                    ->extraModalFooterActions([
                        Action::make('copyModalLink')
                            ->label('Copier le lien')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->color('primary')
                            ->action(function ($livewire, Opportunity $record): void {
                                $url = $record->share_url;
                                $livewire->js("window.navigator.clipboard.writeText('{$url}')");
                                Notification::make()
                                    ->title('Lien officiel copié !')
                                    ->body("Le lien public pour \"{$record->title}\" a été copié dans le presse-papier.")
                                    ->success()
                                    ->send();
                            }),
                        Action::make('modalWhatsapp')
                            ->label('WhatsApp')
                            ->icon('heroicon-o-chat-bubble-left-ellipsis')
                            ->color('success')
                            ->url(fn (Opportunity $record): string => $record->share_links['whatsapp'])
                            ->openUrlInNewTab(),
                        Action::make('modalLinkedin')
                            ->label('LinkedIn')
                            ->icon('heroicon-o-globe-alt')
                            ->color('info')
                            ->url(fn (Opportunity $record): string => $record->share_links['linkedin'])
                            ->openUrlInNewTab(),
                        Action::make('modalTwitter')
                            ->label('X (Twitter)')
                            ->icon('heroicon-o-hashtag')
                            ->color('gray')
                            ->url(fn (Opportunity $record): string => $record->share_links['twitter'])
                            ->openUrlInNewTab(),
                    ]),

                ActionGroup::make([
                    Action::make('copyDirectLink')
                        ->label('Copier le lien public')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('warning')
                        ->action(function ($livewire, Opportunity $record): void {
                            $url = $record->share_url;
                            $livewire->js("window.navigator.clipboard.writeText('{$url}')");
                            Notification::make()
                                ->title('Lien copié !')
                                ->body("Le lien a été copié dans le presse-papier : {$url}")
                                ->success()
                                ->send();
                        }),

                    Action::make('shareWhatsappGroup')
                        ->label('Partager sur WhatsApp')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('emerald')
                        ->url(fn (Opportunity $record): string => $record->share_links['whatsapp'])
                        ->openUrlInNewTab(),

                    Action::make('shareLinkedinGroup')
                        ->label('Partager sur LinkedIn')
                        ->icon('heroicon-o-globe-alt')
                        ->color('info')
                        ->url(fn (Opportunity $record): string => $record->share_links['linkedin'])
                        ->openUrlInNewTab(),

                    Action::make('shareTwitterGroup')
                        ->label('Partager sur X (Twitter)')
                        ->icon('heroicon-o-hashtag')
                        ->color('gray')
                        ->url(fn (Opportunity $record): string => $record->share_links['twitter'])
                        ->openUrlInNewTab(),

                    Action::make('view_front')
                        ->label('Voir sur le site public')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->color('gray')
                        ->url(fn (Opportunity $record): string => $record->share_url)
                        ->openUrlInNewTab(),

                    ViewAction::make()->slideOver(),
                    EditAction::make()->slideOver(),
                    DeleteAction::make(),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
