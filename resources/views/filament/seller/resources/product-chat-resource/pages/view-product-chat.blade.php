<x-filament-panels::page>
    <style>
        /* Переопределяем стили Filament - только для контента */
        .fi-page-content > div {
            max-width: 100% !important;
            width: 100% !important;
        }
        
        .fi-header {
            display: none !important;
        }
        
        :root {
            --chat-bg: #0f0f0f;
            --chat-surface: #171717;
            --chat-border: rgba(255, 255, 255, 0.08);
            --chat-text: #e5e5e5;
            --chat-text-muted: #a1a1aa;
            --chat-accent: #f59e0b;
            --chat-admin-bg: rgba(59, 130, 246, 0.1);
            --chat-admin-border: rgba(59, 130, 246, 0.2);
            --chat-user-bg: rgba(255, 255, 255, 0.03);
            --chat-user-border: rgba(255, 255, 255, 0.08);
        }
        
        .admin-chat-page {
            max-width: 100%;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Ticket Header */
        .ticket-info-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border: 1px solid var(--chat-border);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        
        .ticket-info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--chat-accent), transparent);
            opacity: 0.5;
        }
        
        .ticket-info-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .ticket-info-main {
            flex: 1;
        }
        
        .ticket-id {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--chat-text-muted);
            font-family: ui-monospace, monospace;
            margin-bottom: 8px;
        }
        
        .ticket-subject {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--chat-text);
            margin: 0 0 12px;
            line-height: 1.3;
        }
        
        .ticket-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        
        .ticket-badge {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        
        .badge-status-open { background: rgba(250, 204, 21, 0.15); border: 1px solid rgba(250, 204, 21, 0.3); color: #facc15; }
        .badge-status-in_progress { background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); color: #3b82f6; }
        .badge-status-resolved { background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #22c55e; }
        .badge-status-closed { background: rgba(161, 161, 170, 0.15); border: 1px solid rgba(161, 161, 170, 0.3); color: #a1a1aa; }
        
        .badge-type-chat { background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); color: #3b82f6; }
        
        .ticket-customer {
            font-size: 0.875rem;
            color: var(--chat-text-muted);
        }
        
        .ticket-customer strong {
            color: var(--chat-text);
        }
        
        .ticket-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }
        
        .btn-action-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        
        .btn-action-danger:hover {
            background: rgba(239, 68, 68, 0.25);
        }
        
        .btn-action-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }
        
        .btn-action-success:hover {
            background: rgba(34, 197, 94, 0.25);
        }
        
        /* Chat Container */
        .chat-container {
            background: linear-gradient(135deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border: 1px solid var(--chat-border);
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 350px);
            min-height: 500px;
        }
        
        /* Messages Area */
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .messages-area::-webkit-scrollbar {
            width: 6px;
        }
        
        .messages-area::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .messages-area::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }
        
        /* Message Bubble */
        .message-bubble {
            display: flex;
            gap: 12px;
            max-width: 80%;
            animation: messageSlideIn 0.3s ease;
        }
        
        @keyframes messageSlideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message-bubble.admin-message {
            margin-left: auto;
            flex-direction: row-reverse;
        }
        
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid var(--chat-border);
        }
        
        .message-content {
            flex: 1;
            min-width: 0;
        }
        
        .message-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
            font-size: 0.813rem;
        }
        
        .message-author {
            font-weight: 600;
            color: var(--chat-text);
        }
        
        .message-badge-admin {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.625rem;
            font-weight: 600;
            text-transform: uppercase;
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }
        
        .message-time {
            color: var(--chat-text-muted);
            font-size: 0.75rem;
        }
        
        .message-text {
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 0.938rem;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .user-message .message-text {
            background: var(--chat-user-bg);
            border: 1px solid var(--chat-user-border);
            color: var(--chat-text);
        }
        
        .admin-message .message-text {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(59, 130, 246, 0.06));
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: var(--chat-text);
        }
        
        /* Attachments */
        .message-attachments {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
        }
        
        .attachment-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--chat-border);
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .attachment-item:hover {
            background: rgba(0, 0, 0, 0.4);
            border-color: var(--chat-accent);
        }
        
        .attachment-icon {
            width: 20px;
            height: 20px;
            color: var(--chat-accent);
        }
        
        .attachment-name {
            font-size: 0.813rem;
            color: var(--chat-text);
        }
        
        .attachment-image {
            max-width: 300px;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 8px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .attachment-image:hover {
            transform: scale(1.02);
        }
        
        /* Reply Form */
        .reply-form {
            border-top: 1px solid var(--chat-border);
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
        }
        
        .reply-textarea {
            width: 100%;
            min-height: 100px;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--chat-border);
            border-radius: 12px;
            color: var(--chat-text);
            font-size: 0.938rem;
            resize: vertical;
            transition: all 0.2s ease;
        }
        
        .reply-textarea:focus {
            outline: none;
            border-color: var(--chat-accent);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }
        
        .reply-textarea::placeholder {
            color: var(--chat-text-muted);
        }
        
        .reply-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 14px;
            gap: 12px;
        }
        
        .btn-attach {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: transparent;
            border: 1px solid var(--chat-border);
            border-radius: 8px;
            color: var(--chat-text-muted);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-attach:hover {
            background: rgba(255, 255, 255, 0.03);
            border-color: var(--chat-accent);
            color: var(--chat-accent);
        }
        
        .btn-attach svg {
            width: 18px;
            height: 18px;
        }
        
        .btn-send {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--chat-accent), #d97706);
            border: none;
            border-radius: 8px;
            color: #000;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.3);
        }
        
        .btn-send svg {
            width: 18px;
            height: 18px;
        }
        
        /* Attachments Preview */
        .attachments-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        
        .attachment-preview-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            border-radius: 6px;
            font-size: 0.813rem;
            color: var(--chat-text);
        }
        
        .attachment-remove {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            padding: 2px;
            display: flex;
            align-items: center;
        }
        
        .attachment-remove:hover {
            color: #f87171;
        }
        
        /* Empty State */
        .empty-messages {
            text-align: center;
            padding: 40px;
            color: var(--chat-text-muted);
        }
        
        .empty-messages svg {
            width: 48px;
            height: 48px;
            margin-bottom: 12px;
            opacity: 0.5;
        }
        
        /* Closed Chat Alert */
        .chat-closed-alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            background: rgba(250, 204, 21, 0.1);
            border: 1px solid rgba(250, 204, 21, 0.2);
            border-radius: 8px;
            color: #facc15;
            font-size: 0.875rem;
            margin-bottom: 16px;
        }
        
        .chat-closed-alert svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
    </style>

    <div class="admin-chat-page" style="max-width: 100% !important; width: 100% !important; margin: 0 !important; padding: 0 20px !important;">
        <!-- Chat Info Card -->
        <div class="ticket-info-card" style="max-width: 100% !important;">
            <div class="ticket-info-header">
                <div class="ticket-info-main">
                    <div class="ticket-id">Product Chat #{{ $this->record->id }}</div>
                    <h1 class="ticket-subject">{{ $this->record->product->name }}</h1>
                    <div class="ticket-badges">
                        <span class="ticket-badge badge-status-{{ $this->record->status }}">
                            {{ ucfirst(str_replace('_', ' ', $this->record->status)) }}
                        </span>
                        <span class="ticket-badge badge-type-chat">
                            Product Chat
                        </span>
                    </div>
                    <div class="ticket-customer">
                        Customer: <strong>{{ $this->record->customer->name }}</strong> ({{ $this->record->customer->email }})
                    </div>
                </div>
                
                <div class="ticket-actions">
                    @if($this->record->status === 'open')
                        <button 
                            wire:click="closeChat"
                            wire:confirm="Are you sure you want to close this chat?"
                            class="btn-action btn-action-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Close Chat
                        </button>
                    @else
                        <button 
                            wire:click="reopenChat"
                            class="btn-action btn-action-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reopen Chat
                        </button>
                    @endif
                </div>
            </div>
        </div>
        
        @if($this->record->status === 'closed')
            <div class="chat-closed-alert">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                This chat is closed. Reopen it to continue the conversation.
            </div>
        @endif

        <!-- Chat Container -->
        <div class="chat-container" style="max-width: 100% !important; width: 100% !important;">
            <!-- Messages Area with polling -->
            <div class="messages-area" id="messagesArea" wire:poll.3s>
                @forelse($this->record->messages as $message)
                    <div class="message-bubble {{ $message->is_seller ? 'admin-message' : 'user-message' }}">
                        <img 
                            src="{{ $message->user->avatar ? asset('storage/'.$message->user->avatar) : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($message->user->email))).'?s=80&d=identicon' }}" 
                            alt="{{ $message->user->name }}" 
                            class="message-avatar"
                        >
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-author">{{ $message->user->name }}</span>
                                @if($message->is_seller)
                                    <span class="message-badge-admin">{{ __('common.seller') }}</span>
                                @endif
                                <span class="message-time">{{ $message->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="message-text">{{ $message->message }}</div>
                            
                            @if($message->attachment_path)
                                <div class="message-attachments">
                                    @php
                                        $isImage = str_starts_with($message->attachment_type ?? '', 'image/');
                                    @endphp
                                    @if($isImage)
                                        <a href="{{ asset('storage/' . $message->attachment_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $message->attachment_path) }}" alt="{{ $message->attachment_name }}" class="attachment-image">
                                        </a>
                                    @else
                                        <a href="{{ asset('storage/' . $message->attachment_path) }}" target="_blank" class="attachment-item">
                                            <svg class="attachment-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            <span class="attachment-name">{{ $message->attachment_name }}</span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-messages">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <div>{{ __('common.no_messages_yet') }}</div>
                    </div>
                @endforelse
            </div>

            <!-- Reply Form -->
            @if($this->record->status !== 'closed')
                <form wire:submit="sendMessage" class="reply-form">
                    <textarea 
                        wire:model="newMessage" 
                        class="reply-textarea"
                        placeholder="{{ __('common.type_your_reply') }}"
                    ></textarea>
                    
                    <div class="reply-actions">
                        <label class="btn-attach">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            {{ __('common.attach_files') }}
                            <input type="file" wire:model="attachments" multiple accept="image/*,.pdf,.doc,.docx,.txt" style="display: none;">
                        </label>
                        
                        <button type="submit" class="btn-send">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            {{ __('common.send_reply') }}
                        </button>
                    </div>
                    
                    @if(!empty($attachments) && count($attachments) > 0)
                        <div class="attachments-preview">
                            @foreach($attachments as $index => $file)
                                <div class="attachment-preview-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    {{ $file->getClientOriginalName() }}
                                    <button type="button" wire:click="removeAttachment({{ $index }})" class="attachment-remove">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </form>
            @else
                <div style="padding: 20px; text-align: center; color: var(--chat-text-muted); border-top: 1px solid var(--chat-border);">
                    This chat is closed. Reopen it to send messages.
                </div>
            @endif
        </div>
    </div>

    @script
    <script>
        function scrollToBottom() {
            const messagesArea = document.getElementById('messagesArea');
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
