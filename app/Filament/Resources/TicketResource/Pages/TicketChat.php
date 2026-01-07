<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Notifications\TicketReplied;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;

class TicketChat extends Page
{
    use WithFileUploads;

    protected static string $resource = TicketResource::class;

    protected static string $view = 'filament.resources.ticket-resource.pages.ticket-chat';

    public $record;

    public $newMessage = '';
    
    public $attachments = [];
    
    public $messagesCount = 0;

    public function mount($record): void
    {
        $this->record = Ticket::with(['messages.user', 'messages.attachments', 'user'])->findOrFail($record);
        $this->messagesCount = $this->record->messages()->count();
        
        // Mark all unread messages as read
        $this->record->messages()
            ->where('is_admin_reply', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
    
    /**
     * Polling method - check for new messages every 3 seconds
     */
    public function checkNewMessages(): void
    {
        $currentCount = $this->record->messages()->count();
        
        if ($currentCount > $this->messagesCount) {
            $this->messagesCount = $currentCount;
            $this->record->refresh();
            $this->record->load(['messages.user', 'messages.attachments']);
            
            // Mark new messages as read
            $this->record->messages()
                ->where('is_admin_reply', false)
                ->where('is_read', false)
                ->update(['is_read' => true]);
                
            $this->dispatch('scroll-to-bottom');
        }
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $message = $this->record->messages()->create([
            'user_id' => Auth::id(),
            'message' => $this->newMessage,
            'is_admin_reply' => true,
        ]);

        // Handle attachments
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $path = $file->store('ticket-attachments', 'public');
                $message->attachments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        $this->record->update([
            'last_reply_at' => now(),
            'status' => $this->record->status === 'open' ? 'in_progress' : $this->record->status,
            'assigned_to' => $this->record->assigned_to ?? Auth::id(),
        ]);

        // Notify user
        try {
            $this->record->user->notify(new TicketReplied($this->record, $message));
        } catch (\Exception $e) {
            \Log::error('Failed to send notification: '.$e->getMessage());
        }

        $this->newMessage = '';
        $this->attachments = [];
        $this->messagesCount = $this->record->messages()->count();
        $this->record->refresh();
        $this->record->load(['messages.user', 'messages.attachments']);

        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Message sent!')
            ->send();
            
        $this->dispatch('scroll-to-bottom');
    }
    
    public function removeAttachment($index)
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }
}
