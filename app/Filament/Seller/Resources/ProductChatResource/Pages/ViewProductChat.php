<?php

namespace App\Filament\Seller\Resources\ProductChatResource\Pages;

use App\Filament\Seller\Resources\ProductChatResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class ViewProductChat extends ViewRecord
{
    use WithFileUploads;

    protected static string $resource = ProductChatResource::class;

    protected static string $view = 'filament.seller.resources.product-chat-resource.pages.view-product-chat';

    public $newMessage = '';

    public $attachments = [];

    protected $listeners = ['refreshChat' => '$refresh'];

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function mount($record): void
    {
        parent::mount($record);

        // Mark messages as read
        $this->record->messages()
            ->where('is_seller', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        // Handle first attachment with the message
        $attachmentData = [];
        $remainingAttachments = [];

        if (! empty($this->attachments)) {
            $firstFile = array_shift($this->attachments);
            $path = $firstFile->store('product-chat-attachments', 'public');
            $attachmentData = [
                'attachment_path' => $path,
                'attachment_name' => $firstFile->getClientOriginalName(),
                'attachment_type' => $firstFile->getMimeType(),
            ];
            $remainingAttachments = $this->attachments;
        }

        // Create main message with first attachment
        $message = $this->record->messages()->create(array_merge([
            'user_id' => Auth::id(),
            'message' => $this->newMessage,
            'is_seller' => true,
        ], $attachmentData));

        // Handle remaining attachments as separate messages
        foreach ($remainingAttachments as $file) {
            $path = $file->store('product-chat-attachments', 'public');

            $this->record->messages()->create([
                'user_id' => Auth::id(),
                'message' => '',
                'is_seller' => true,
                'attachment_path' => $path,
                'attachment_name' => $file->getClientOriginalName(),
                'attachment_type' => $file->getMimeType(),
            ]);
        }

        // Update chat
        $this->record->update([
            'last_message_at' => now(),
            'last_message_by' => Auth::id(),
        ]);

        $this->newMessage = '';
        $this->attachments = [];
        $this->record->refresh();
        $this->record->load(['messages.user']);

        $this->dispatch('message-sent');

        Notification::make()
            ->success()
            ->title('Message sent')
            ->send();
    }

    public function removeAttachment($index)
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function closeChat()
    {
        $this->record->update(['status' => 'closed']);
        $this->record->refresh();

        Notification::make()
            ->success()
            ->title('Chat closed')
            ->body('The customer has been notified.')
            ->send();
    }

    public function reopenChat()
    {
        $this->record->update(['status' => 'open']);
        $this->record->refresh();

        Notification::make()
            ->success()
            ->title('Chat reopened')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('close_chat')
                ->label('Close Chat')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'open')
                ->action(function () {
                    $this->record->update(['status' => 'closed']);

                    Notification::make()
                        ->success()
                        ->title('Chat closed')
                        ->send();
                }),

            Actions\Action::make('reopen_chat')
                ->label('Reopen Chat')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->visible(fn () => $this->record->status === 'closed')
                ->action(function () {
                    $this->record->update(['status' => 'open']);

                    Notification::make()
                        ->success()
                        ->title('Chat reopened')
                        ->send();
                }),
        ];
    }
}
