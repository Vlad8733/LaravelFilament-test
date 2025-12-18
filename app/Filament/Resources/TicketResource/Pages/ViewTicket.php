<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class ViewTicket extends ViewRecord
{
    use WithFileUploads;

    protected static string $resource = TicketResource::class;
    
    protected static string $view = 'filament.resources.ticket-resource.pages.view-ticket';

    public string $newMessage = '';
    public array $attachments = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    
    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }
    
    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,txt,zip',
        ]);

        try {
            $message = $this->record->messages()->create([
                'user_id' => Auth::id(),
                'message' => $this->newMessage,
                'is_admin_reply' => true,
            ]);

            // Обрабатываем вложения
            if (!empty($this->attachments)) {
                foreach ($this->attachments as $file) {
                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('ticket-attachments', $filename, 'public');

                    $message->attachments()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }

            $this->record->update(['last_reply_at' => now()]);

            try {
                $this->record->user->notify(new \App\Notifications\TicketReplied($this->record, $message));
            } catch (\Exception $e) {
                \Log::error('Notification failed: ' . $e->getMessage());
            }

            $this->newMessage = '';
            $this->attachments = [];
            $this->record->load(['messages.user', 'messages.attachments']);
            
            $this->dispatch('message-sent');
            
            \Filament\Notifications\Notification::make()
                ->success()
                ->title('Message sent!')
                ->send();
            
        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage());
            
            \Filament\Notifications\Notification::make()
                ->danger()
                ->title('Failed to send message')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Ticket Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('subject')
                            ->columnSpanFull(),
                        
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->markdown(),
                        
                        Infolists\Components\TextEntry::make('priority')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'low' => 'success',
                                'medium' => 'warning',
                                'high' => 'danger',
                                'urgent' => 'danger',
                                default => 'gray',
                            }),
                        
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'open' => 'info',
                                'in_progress' => 'warning',
                                'resolved' => 'success',
                                'closed' => 'gray',
                                default => 'gray',
                            }),
                        
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Created By'),
                        
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}