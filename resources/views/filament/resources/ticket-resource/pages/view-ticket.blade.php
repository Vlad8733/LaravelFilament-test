<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/tickets/ticket-show.css') }}">
    
    <style>
        /* Переопределяем стили Filament для чистого вида */
        .fi-main { 
            padding: 0 !important; 
            background: transparent !important;
        }
        .ticket-show-page { 
            margin: 0 !important; 
            padding: 0 !important; 
        }
        
        /* Скрываем стандартный header Filament */
        .fi-header {
            display: none;
        }
        
        /* ПОЛНОЕ УДАЛЕНИЕ HOVER И ФИКС ЦВЕТА КНОПКИ */
        button.btn-send,
        button.btn-send:hover,
        button.btn-send:focus,
        button.btn-send:active {
            background: #f59e0b !important;
            color: #0a0a0a !important;
            transform: none !important;
            box-shadow: none !important;
            border: none !important;
        }
    </style>

    <div class="ticket-show-page">
        <div class="ticket-show-container">
            <!-- Ticket Header -->
            <div class="ticket-show-header">
                <div class="ticket-header-top">
                    <div style="flex: 1;">
                        <div class="ticket-show-id">Ticket #{{ $this->record->id }}</div>
                        <h1 class="ticket-show-subject">{{ $this->record->subject }}</h1>
                        
                        <div class="ticket-badges">
                            <span class="badge badge-priority-{{ $this->record->priority }}">
                                {{ ucfirst($this->record->priority) }} Priority
                            </span>
                            <span class="badge badge-status-{{ $this->record->status }}">
                                {{ ucfirst(str_replace('_', ' ', $this->record->status)) }}
                            </span>
                        </div>

                        @if($this->record->description)
                            <div class="ticket-show-description">
                                {{ $this->record->description }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Chat Container -->
            <div class="chat-container">
                <!-- Messages Area -->
                <div class="messages-area" id="adminMessagesArea" wire:poll.3s>
                    @forelse($this->record->messages as $message)
                        <div class="message-bubble {{ $message->is_admin_reply ? 'admin-message' : 'user-message' }}" 
                             data-message-id="{{ $message->id }}">
                            @php
                                $avatarUrl = $message->user->profile_photo_url 
                                    ?? ($message->user->avatar 
                                        ? asset('storage/' . $message->user->avatar) 
                                        : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($message->user->email))) . '?s=80&d=identicon');
                            @endphp
                            
                            <img 
                                src="{{ $avatarUrl }}" 
                                alt="{{ $message->user->name }}"
                                class="message-avatar"
                                onerror="this.src='https://www.gravatar.com/avatar/{{ md5(strtolower(trim($message->user->email))) }}?s=80&d=identicon'"
                            >
                            
                            <div class="message-content">
                                <div class="message-header">
                                    <span class="message-author">{{ $message->user->name }}</span>
                                    @if($message->is_admin_reply)
                                        <span class="message-badge message-badge-admin">Support Team</span>
                                    @endif
                                    <span class="message-time">{{ $message->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="message-text">{!! nl2br(e($message->message)) !!}</div>
                                
                                @if($message->attachments->count() > 0)
                                    <div class="message-attachments">
                                        @foreach($message->attachments as $attachment)
                                            <a href="{{ asset('storage/' . $attachment->file_path) }}" 
                                               target="_blank" 
                                               class="attachment-item">
                                                <svg class="attachment-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                </svg>
                                                <div class="attachment-info">
                                                    <div class="attachment-name">{{ $attachment->file_name }}</div>
                                                    <div class="attachment-size">{{ number_format($attachment->file_size / 1024, 2) }} KB</div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 2rem; color: #6b7280;">
                            No messages yet. Start the conversation!
                        </div>
                    @endforelse
                </div>

                <!-- Reply Form -->
                @if($this->record->status !== 'closed')
                    <div class="reply-form">
                        <form wire:submit="sendMessage">
                            <textarea 
                                wire:model="newMessage"
                                class="reply-textarea"
                                placeholder="Type your message..."
                                required
                            ></textarea>
                            
                            @if(!empty($attachments))
                                <div style="margin-top: 1rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                    @foreach($attachments as $index => $attachment)
                                        <div style="position: relative; padding: 0.5rem 1rem; background: rgba(255,255,255,0.05); border-radius: 8px; border: 1px solid var(--ticket-border); font-size: 0.875rem; color: var(--text-primary);">
                                            <span>{{ $attachment->getClientOriginalName() }}</span>
                                            <button 
                                                type="button"
                                                wire:click="removeAttachment({{ $index }})"
                                                style="margin-left: 0.5rem; color: #ef4444; background: none; border: none; cursor: pointer; font-weight: bold;">
                                                ✕
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            <div class="reply-form-actions">
                                <label class="btn-attach">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                    </svg>
                                    Attach Files
                                    <input 
                                        type="file" 
                                        wire:model="attachments"
                                        multiple
                                        accept="image/*,.pdf,.doc,.docx,.txt,.zip"
                                        style="display: none;"
                                    >
                                </label>

                                <button 
                                    type="submit" 
                                    class="btn-send" 
                                    wire:loading.attr="disabled"
                                    style="background: #f59e0b !important; color: #0a0a0a !important;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="22" y1="2" x2="11" y2="13"></line>
                                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                    </svg>
                                    <span wire:loading.remove>Send Reply</span>
                                    <span wire:loading>Sending...</span>
                                </button>
                            </div>

                            @error('attachments.*')
                                <div class="alert alert-error" style="margin-top: 1rem;">
                                    {{ $message }}
                                </div>
                            @enderror
                        </form>
                    </div>
                @else
                    <div style="padding: 1.5rem; text-align: center; color: #6b7280; border-top: 1px solid var(--ticket-border); background: rgba(255, 255, 255, 0.01);">
                        This ticket is closed
                    </div>
                @endif
            </div>
        </div>
    </div>

    @script
    <script>
        function scrollToBottom() {
            const messagesArea = document.getElementById('adminMessagesArea');
            if (messagesArea) {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }
        }

        setTimeout(scrollToBottom, 300);

        $wire.on('message-sent', () => {
            setTimeout(scrollToBottom, 100);
        });

        document.addEventListener('livewire:update', () => {
            setTimeout(scrollToBottom, 100);
        });
    </script>
    @endscript
</x-filament-panels::page>