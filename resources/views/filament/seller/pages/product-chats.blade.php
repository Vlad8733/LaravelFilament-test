<x-filament-panels::page>
    <style>
        .chat-list-item {
            display: block;
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }
        
        .dark .chat-list-item {
            background: #1f2937;
            border-color: #374151;
        }
        
        .chat-list-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #f59e0b;
        }
        
        .chat-product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }
        
        .chat-info {
            flex: 1;
            min-width: 0;
        }
        
        .chat-product-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.25rem;
        }
        
        .dark .chat-product-name {
            color: white;
        }
        
        .chat-customer {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .chat-last-message {
            margin-top: 0.75rem;
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: 8px;
        }
        
        .dark .chat-last-message {
            background: #111827;
        }
        
        .chat-message-text {
            font-size: 0.875rem;
            color: #374151;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .dark .chat-message-text {
            color: #d1d5db;
        }
        
        .unread-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.75rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            flex-shrink: 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-state-icon {
            width: 3rem;
            height: 3rem;
            margin: 0 auto 1rem;
            color: #9ca3af;
        }
    </style>

    <div class="space-y-4">
        @php
            $chats = $this->getChats();
        @endphp

        @if($chats->count() > 0)
            @foreach($chats as $chat)
                <a href="{{ route('product-chat.show', $chat->product) }}" class="chat-list-item">
                    <div style="display: flex; gap: 1rem; align-items: flex-start;">
                        <!-- Product Image -->
                        <img src="{{ $chat->product->primary_image_url }}" 
                             alt="{{ $chat->product->name }}"
                             class="chat-product-image">
                        
                        <!-- Chat Info -->
                        <div class="chat-info">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.5rem;">
                                <div style="flex: 1;">
                                    <h3 class="chat-product-name">{{ $chat->product->name }}</h3>
                                    <p class="chat-customer">
                                        Customer: <span style="font-weight: 500;">{{ $chat->customer->name }}</span>
                                    </p>
                                </div>
                                
                                @if($chat->unread_messages_count > 0)
                                    <span class="unread-badge">
                                        {{ $chat->unread_messages_count }} new
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Last Message -->
                            @if($chat->latestMessage->first())
                                <div class="chat-last-message">
                                    <p class="chat-message-text">
                                        <span style="font-weight: 500;">{{ $chat->lastMessageBy->name }}:</span>
                                        {{ $chat->latestMessage->first()->message }}
                                    </p>
                                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                                        {{ $chat->last_message_at->diffForHumans() }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        @else
            <div class="empty-state">
                <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <h3 style="font-size: 0.875rem; font-weight: 500; color: #111827; margin-bottom: 0.25rem;" class="dark:text-white">
                    No chats yet
                </h3>
                <p style="font-size: 0.875rem; color: #6b7280;">
                    Customers will be able to contact you about your products.
                </p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
