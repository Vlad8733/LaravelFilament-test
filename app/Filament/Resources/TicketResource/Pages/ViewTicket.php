<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Notifications\TicketReplied;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

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

            Actions\Action::make('reply')
                ->label('Send Reply')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    Textarea::make('message')
                        ->label('Your Reply')
                        ->required()
                        ->rows(4),
                    FileUpload::make('attachments')
                        ->label('Attachments')
                        ->multiple()
                        ->directory('ticket-attachments')
                        ->maxFiles(5)
                        ->maxSize(10240),
                ])
                ->action(function (array $data) {
                    $ticket = $this->record;

                    // Создаём сообщение
                    $message = $ticket->messages()->create([
                        'user_id' => Auth::id(),
                        'message' => $data['message'],
                        'is_admin_reply' => true,
                    ]);

                    // Обрабатываем вложения
                    if (! empty($data['attachments'])) {
                        foreach ($data['attachments'] as $path) {
                            $message->attachments()->create([
                                'file_name' => basename($path),
                                'file_path' => $path,
                                'file_type' => null,
                                'file_size' => null,
                            ]);
                        }
                    }

                    // Обновляем статус и время
                    $ticket->update([
                        'status' => 'in_progress',
                        'last_reply_at' => now(),
                        'assigned_to' => Auth::id(),
                    ]);

                    // Отправляем уведомление пользователю
                    $ticket->user->notify(new TicketReplied($ticket, $message));

                    Notification::make()
                        ->title('Reply sent successfully')
                        ->success()
                        ->send();
                }),

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
