<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Notifications\TicketReplied;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class TicketChat extends Page
{
    protected static string $resource = TicketResource::class;

    protected static string $view = 'filament.resources.ticket-resource.pages.ticket-chat';

    public $record;

    public $newMessage = '';

    public function mount($record): void
    {
        $this->record = Ticket::with(['messages.user', 'messages.attachments', 'user'])->findOrFail($record);
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
        ]);

        $message = $this->record->messages()->create([
            'user_id' => Auth::id(),
            'message' => $this->newMessage,
            'is_admin_reply' => true,
        ]);

        $this->record->update(['last_reply_at' => now()]);

        // Уведомляем пользователя о новом ответе
        try {
            $this->record->user->notify(new TicketReplied($this->record, $message));
        } catch (\Exception $e) {
            \Log::error('Failed to send notification: '.$e->getMessage());
        }

        $this->newMessage = '';
        $this->record->refresh();
        $this->record->load(['messages.user', 'messages.attachments']);

        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Message sent!')
            ->send();
    }
}
