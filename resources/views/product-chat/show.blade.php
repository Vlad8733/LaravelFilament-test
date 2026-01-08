@extends('layouts.app')

@section('title', __('product_chat.title'))

@push('styles')
    @vite('resources/css/tickets/tickets.css')
    @vite('resources/css/tickets/ticket-show.css')
@endpush

@section('content')
<div class="ticket-show-page">
    <div class="ticket-show-container">
        <!-- Header -->
        <div class="ticket-show-header">
            <div class="ticket-header-top">
                <div class="ticket-header-info">
                    <div class="ticket-show-id">{{ __('product_chat.product_chat') }} #{{ $chat->id }}</div>
                    <h1 class="ticket-show-subject">{{ $product->name }}</h1>
                    <div class="ticket-show-badges">
                        <span class="badge badge-status-{{ $chat->status }}">
                            {{ $chat->status === 'open' ? __('product_chat.status_open') : __('product_chat.status_closed') }}
                        </span>
                        <span class="badge badge-priority-medium">
                            {{ __('product_chat.product_chat') }}
                        </span>
                    </div>
                    <div class="ticket-show-description">
                        {{ __('product_chat.seller') }}: {{ $chat->seller->name }}@if($product->company)<br>{{ __('common.company') }}: {{ $product->company->name }}@endif
                    </div>
                </div>
                <div class="ticket-header-actions">
                    <a href="{{ route('products.show', $product->slug) }}" class="btn-action btn-action-back">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        {{ __('product_chat.back_to_product') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if($chat->status === 'closed')
            <div class="alert alert-warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                {{ __('product_chat.chat_closed_alert') }}
            </div>
        @endif

        <!-- Chat Container -->
        <div class="chat-container">
            <!-- Messages Area -->
            <div class="messages-area" id="messagesArea" data-messages-container>
                @forelse($chat->messages as $message)
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
                                    <span class="message-badge message-badge-admin">{{ __('product_chat.seller') }}</span>
                                @endif
                                <span class="message-time">{{ $message->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="message-text">{{ $message->message }}</div>
                            
                            @if($message->attachment_url)
                                <div class="message-attachments">
                                    @if($message->isImage())
                                        <a href="{{ $message->attachment_url }}" target="_blank" class="attachment-image-link">
                                            <img src="{{ $message->attachment_url }}" alt="{{ $message->attachment_name }}" class="attachment-image-preview">
                                        </a>
                                    @else
                                        <a href="{{ $message->attachment_url }}" target="_blank" class="attachment-item">
                                            <svg class="attachment-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            <div class="attachment-info">
                                                <div class="attachment-name">{{ $message->attachment_name }}</div>
                                            </div>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                        {{ __('product_chat.no_messages_yet') }}
                    </div>
                @endforelse
            </div>

            <!-- Reply Form -->
            @if($chat->status !== 'closed')
                <form action="{{ route('product-chat.send', $chat) }}" method="POST" enctype="multipart/form-data" class="reply-form" id="replyForm">
                    @csrf
                    <textarea 
                        name="message" 
                        id="messageTextarea"
                        class="reply-textarea" 
                        placeholder="{{ __('product_chat.type_your_message') }}"
                        required
                    ></textarea>
                    
                    <div class="reply-form-actions">
                        <label for="attachmentInput" class="btn-attach">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                            </svg>
                            {{ __('product_chat.attach_files') }}
                            <input type="file" name="attachment" id="attachmentInput" accept="image/*,.pdf,.doc,.docx" style="display: none;">
                        </label>
                        
                        <button type="submit" class="btn-send" id="sendBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                            <span id="sendBtnText">{{ __('product_chat.send_message') }}</span>
                        </button>
                    </div>
                    
                    <div id="attachmentsList" style="margin-top: 1rem;"></div>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesArea = document.getElementById('messagesArea');
    const replyForm = document.getElementById('replyForm');
    const messageTextarea = document.getElementById('messageTextarea');
    const sendBtn = document.getElementById('sendBtn');
    const sendBtnText = document.getElementById('sendBtnText');
    const attachmentInput = document.getElementById('attachmentInput');
    const attachmentsList = document.getElementById('attachmentsList');
    
    // Scroll to bottom on load
    if (messagesArea) {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }
    
    // Handle file selection display
    if (attachmentInput) {
        attachmentInput.addEventListener('change', function() {
            attachmentsList.innerHTML = '';
            if (this.files.length > 0) {
                attachmentsList.innerHTML = '<div style="font-size: 0.875rem; color: var(--text-secondary);">{{ __('product_chat.selected_files') }}</div>';
                Array.from(this.files).forEach(file => {
                    const fileDiv = document.createElement('div');
                    fileDiv.style.cssText = 'padding: 0.5rem; background: rgba(255,255,255,0.02); border-radius: 6px; margin-top: 0.5rem; font-size: 0.875rem;';
                    fileDiv.textContent = file.name;
                    attachmentsList.appendChild(fileDiv);
                });
            }
        });
    }
    
    // AJAX form submission
    if (replyForm) {
        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(replyForm);
            const message = messageTextarea.value.trim();
            
            if (!message) return;
            
            // Disable button
            sendBtn.disabled = true;
            sendBtnText.textContent = '{{ __('product_chat.sending') }}';
            
            fetch(replyForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.message) {
                    const msg = data.message;
                    appendMessage(msg);
                    lastMessageId = msg.id;
                    
                    // Clear form
                    messageTextarea.value = '';
                    attachmentInput.value = '';
                    attachmentsList.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __('product_chat.error_sending') }}');
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtnText.textContent = '{{ __('product_chat.send_message') }}';
            });
        });
    }
});
</script>

@push('scripts')
<script>
let lastMessageId = {{ $chat->messages->last()?->id ?? 0 }};

function scrollToBottom() {
    const messagesArea = document.getElementById('messagesArea');
    if (messagesArea) {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }
}

function appendMessage(msg) {
    const container = document.getElementById('messagesArea');
    if (!container) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message-bubble ' + (msg.is_seller ? 'admin-message' : 'user-message');
    
    let avatarUrl = msg.user_avatar || 'https://www.gravatar.com/avatar/?s=80&d=identicon';
    let userName = msg.user_name || 'User';
    
    // Build attachment HTML if exists
    let attachmentHtml = '';
    if (msg.attachment_url) {
        if (msg.is_image) {
            attachmentHtml = `
                <div class="message-attachments">
                    <a href="${msg.attachment_url}" target="_blank" class="attachment-image-link">
                        <img src="${msg.attachment_url}" alt="${escapeHtml(msg.attachment_name || 'Image')}" class="attachment-image-preview">
                    </a>
                </div>
            `;
        } else {
            attachmentHtml = `
                <div class="message-attachments">
                    <a href="${msg.attachment_url}" target="_blank" class="attachment-item">
                        <svg class="attachment-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        <div class="attachment-info">
                            <div class="attachment-name">${escapeHtml(msg.attachment_name || 'File')}</div>
                        </div>
                    </a>
                </div>
            `;
        }
    }
    
    messageDiv.innerHTML = `
        <img src="${avatarUrl}" alt="${escapeHtml(userName)}" class="message-avatar">
        <div class="message-content">
            <div class="message-header">
                <span class="message-author">${escapeHtml(userName)}</span>
                ${msg.is_seller ? '<span class="message-badge message-badge-admin">{{ __('product_chat.seller') }}</span>' : ''}
                <span class="message-time">${msg.created_at || '{{ __('just now') }}'}</span>
            </div>
            <div class="message-text">${escapeHtml(msg.message)}</div>
            ${attachmentHtml}
        </div>
    `;
    
    container.appendChild(messageDiv);
    scrollToBottom();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function checkNewMessages() {
    fetch('{{ route('product-chat.check-new', $chat) }}?after=' + lastMessageId, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(d => {
        if (d.messages && d.messages.length > 0) {
            d.messages.forEach(msg => {
                appendMessage(msg);
                lastMessageId = msg.id;
            });
        }
    })
    .catch(error => console.error('Error:', error));
}

// Poll for new messages every 3 seconds
setInterval(checkNewMessages, 3000);
</script>
@endpush
@endsection
