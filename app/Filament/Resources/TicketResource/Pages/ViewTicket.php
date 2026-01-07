<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('openChat')
                ->label('Open Chat')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->url(fn ($record) => TicketResource::getUrl('chat', ['record' => $record]))
                ->color('success'),

            Actions\Action::make('close')
                ->label('Close Ticket')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status !== 'closed')
                ->action(function () {
                    $this->record->update(['status' => 'closed']);

                    Notification::make()
                        ->title('Ticket closed')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reopen')
                ->label('Reopen Ticket')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->visible(fn () => $this->record->status === 'closed')
                ->action(function () {
                    $this->record->update(['status' => 'open']);

                    Notification::make()
                        ->title('Ticket reopened')
                        ->success()
                        ->send();
                }),
        ];
    }
}
