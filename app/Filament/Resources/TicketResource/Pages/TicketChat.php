<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class TicketChat extends Page
{
    protected static string $resource = TicketResource::class;

    protected static string $view = 'filament.pages.ticket-chat';

    public Ticket $record;
    
    public string $newMessage = '';

    public function mount(int|string $record): void
    {
        $this->record = Ticket::with(['messages.user', 'messages.attachments', 'user'])->findOrFail($record);
    }

    public function sendMessage(): void
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
        ]);

        try {
            $message = $this->record->messages()->create([
                'user_id' => Auth::id(),
                'message' => $this->newMessage,
                'is_admin_reply' => true,
            ]);

            $this->record->update(['last_reply_at' => now()]);

            try {
                $this->record->user->notify(new \App\Notifications\TicketReplied($this->record, $message));
            } catch (\Exception $e) {
                \Log::error('Notification failed: ' . $e->getMessage());
            }

            $this->newMessage = '';
            $this->record->load(['messages.user', 'messages.attachments']);
            
            \Filament\Notifications\Notification::make()
                ->success()
                ->title('Message sent!')
                ->send();
            
        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage());
            
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Failed to send message')
                ->send();
        }
    }
}
