@extends('layouts.app')

@section('title', __('notifications.title'))

@push('styles')
    @vite('resources/css/notifications/notifications.css')
@endpush

@section('content')
<div class="notifications-page">
    <div class="notifications-container">
        <!-- Header -->
        <div class="notifications-header">
            <div class="notifications-header-left">
                <div class="notifications-header-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                        <path d="M160-200v-80h80v-280q0-83 50-147.5T420-792v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q80 20 130 84.5T720-560v280h80v80H160Zm320-300Zm0 420q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80ZM320-280h320v-280q0-66-47-113t-113-47q-66 0-113 47t-47 113v280Z"/>
                    </svg>
                </div>
                <div class="notifications-header-content">
                    <h1 class="notifications-title">{{ __('notifications.title') }}</h1>
                    <p class="notifications-subtitle">
                        @if($notifications->total() > 0)
                            {{ __('notifications.showing_count', ['count' => $notifications->total()]) }}
                        @else
                            {{ __('notifications.empty_subtitle') }}
                        @endif
                    </p>
                </div>
            </div>
            
            @if($notifications->total() > 0)
                <div class="notifications-actions">
                    <button onclick="markAllAsRead()" class="btn-notification-action">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                            <path d="M268-240 42-466l57-56 170 170 56 56-57 56Zm226 0L268-466l56-57 170 170 368-368 56 57-424 424Zm0-226-57-56 198-198 57 56-198 198Z"/>
                        </svg>
                        {{ __('notifications.mark_all_read') }}
                    </button>
                    <button onclick="deleteAll()" class="btn-notification-action danger">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                        </svg>
                        {{ __('notifications.delete_all') }}
                    </button>
                </div>
            @endif
        </div>

        @if($notifications->count() > 0)
            <!-- Filters -->
            <div class="notifications-filters">
                <button class="notification-filter-btn active" data-filter="all">
                    {{ __('notifications.filter_all') }}
                    <span class="notification-filter-badge">{{ $notifications->total() }}</span>
                </button>
                <button class="notification-filter-btn" data-filter="tickets">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    {{ __('notifications.filter_tickets') }}
                </button>
                <button class="notification-filter-btn" data-filter="orders">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                    {{ __('notifications.filter_orders') }}
                </button>
            </div>

            <!-- Notifications List -->
            <div class="notifications-list">
                @foreach($notifications as $notification)
                    @php
                        $type = 'default';
                        $typeLabel = __('notifications.type_notification');
                        
                        if (str_contains($notification->type, 'Ticket')) {
                            $type = 'ticket';
                            $typeLabel = __('notifications.type_ticket');
                        } elseif (str_contains($notification->type, 'Order')) {
                            $type = 'order';
                            $typeLabel = __('notifications.type_order');
                        } elseif (str_contains($notification->type, 'Import')) {
                            $type = 'import';
                            $typeLabel = __('notifications.type_import');
                        } elseif (str_contains($notification->type, 'Refund')) {
                            $type = 'refund';
                            $typeLabel = __('notifications.type_refund');
                        }
                        
                        $isUnread = !$notification->read_at;
                    @endphp
                    
                    <div class="notification-card {{ $isUnread ? 'unread' : '' }}" 
                         id="notification-{{ $notification->id }}"
                         data-type="{{ $type }}">
                        <div class="notification-card-inner">
                            <!-- Icon -->
                            <div class="notification-icon-wrapper {{ $type }} {{ $isUnread ? 'unread-dot' : '' }}">
                                @if($type === 'ticket')
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                @elseif($type === 'order')
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                    </svg>
                                @elseif($type === 'import')
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                        <path d="M440-320v-326L336-542l-56-58 200-200 200 200-56 58-104-104v326h-80ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z"/>
                                    </svg>
                                @elseif($type === 'refund')
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                        <path d="M640-520q17 0 28.5-11.5T680-560q0-17-11.5-28.5T640-600q-17 0-28.5 11.5T600-560q0 17 11.5 28.5T640-520Zm-320-80h200v-80H320v80ZM180-120q-34-114-67-227.5T80-580q0-92 64-156t156-64h200q29-38 70.5-59t89.5-21q25 0 42.5 17.5T720-820q0 6-1.5 12t-3.5 11q-4 11-7.5 22.5T702-752l94 94h84v240H728l-60 60-62-62 122-122v-86h-6L598-504q-14 2-28.5 3t-29.5 1H320q-66 0-113 47t-47 113q0 65 28 149.5T260-40l-80-80Zm340 80L360-200l80-80 80 80 158-158 78 78-236 240Z"/>
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                        <path d="M160-200v-80h80v-280q0-83 50-147.5T420-792v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q80 20 130 84.5T720-560v280h80v80H160Zm320-300Zm0 420q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80ZM320-280h320v-280q0-66-47-113t-113-47q-66 0-113 47t-47 113v280Z"/>
                                    </svg>
                                @endif
                            </div>
                            
                            <!-- Content -->
                            <div class="notification-content">
                                <div class="notification-header">
                                    <span class="notification-type-label {{ $type }}">{{ $typeLabel }}</span>
                                </div>
                                <p class="notification-message">
                                    {{ $notification->data['message'] ?? __('notifications.no_message') }}
                                </p>
                                <div class="notification-meta">
                                    <span class="notification-time">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                            <path d="m612-292 56-56-148-148v-184h-80v216l172 172ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                                        </svg>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="notification-card-actions">
                            @if(isset($notification->data['ticket_id']))
                                <a href="{{ route('tickets.show', $notification->data['ticket_id']) }}" 
                                   class="btn-notification btn-notification-primary ticket">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                        <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
                                    </svg>
                                    {{ __('notifications.view_ticket') }}
                                </a>
                            @elseif(isset($notification->data['order_number']))
                                <a href="{{ route('orders.tracking.show', $notification->data['order_number']) }}" 
                                   class="btn-notification btn-notification-primary order">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                        <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
                                    </svg>
                                    {{ __('notifications.view_order') }}
                                </a>
                            @elseif(isset($notification->data['url']))
                                <a href="{{ $notification->data['url'] }}" 
                                   class="btn-notification btn-notification-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                        <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
                                    </svg>
                                    {{ __('notifications.view') }}
                                </a>
                            @endif
                            
                            @if($isUnread)
                                <button onclick="markAsRead('{{ $notification->id }}')" 
                                        class="btn-notification btn-notification-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                        <path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/>
                                    </svg>
                                    {{ __('notifications.mark_read') }}
                                </button>
                            @endif
                            
                            <div class="notification-actions-spacer"></div>
                            
                            <button onclick="deleteNotification('{{ $notification->id }}')" 
                                    class="btn-notification btn-notification-ghost" 
                                    title="{{ __('notifications.delete') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                                    <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="notifications-pagination">
                {{ $notifications->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="notifications-empty">
                <div class="notifications-empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                        <path d="M160-200v-80h80v-280q0-83 50-147.5T420-792v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q80 20 130 84.5T720-560v280h80v80H160Zm320-300Zm0 420q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80ZM320-280h320v-280q0-66-47-113t-113-47q-66 0-113 47t-47 113v280Z"/>
                    </svg>
                </div>
                <h2 class="notifications-empty-title">{{ __('notifications.no_notifications') }}</h2>
                <p class="notifications-empty-description">{{ __('notifications.empty_message') }}</p>
                <div class="notifications-empty-actions">
                    <a href="{{ route('products.index') }}" class="btn-empty-action">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                            <path d="M240-80q-33 0-56.5-23.5T160-160v-480q0-33 23.5-56.5T240-720h80q0-66 47-113t113-47q66 0 113 47t47 113h80q33 0 56.5 23.5T800-640v480q0 33-23.5 56.5T720-80H240Zm0-80h480v-480H240v480Zm240-560q33 0 56.5-23.5T560-800q0-33-23.5-56.5T480-880q-33 0-56.5 23.5T400-800q0 33 23.5 56.5T480-720ZM240-160v-480 480Z"/>
                        </svg>
                        {{ __('notifications.browse_products') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
// Filter functionality
document.querySelectorAll('.notification-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.dataset.filter;
        
        // Update active state
        document.querySelectorAll('.notification-filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Filter cards
        document.querySelectorAll('.notification-card').forEach(card => {
            if (filter === 'all') {
                card.style.display = 'block';
            } else if (filter === 'tickets' && card.dataset.type === 'ticket') {
                card.style.display = 'block';
            } else if (filter === 'orders' && card.dataset.type === 'order') {
                card.style.display = 'block';
            } else if (filter !== 'all') {
                card.style.display = 'none';
            }
        });
    });
});

async function markAsRead(id) {
    try {
        const response = await fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        
        if (response.ok) {
            const card = document.getElementById(`notification-${id}`);
            card.classList.remove('unread');
            
            // Remove unread dot
            const iconWrapper = card.querySelector('.notification-icon-wrapper');
            if (iconWrapper) {
                iconWrapper.classList.remove('unread-dot');
            }
            
            // Hide mark as read button
            const markBtn = card.querySelector('.btn-notification-secondary');
            if (markBtn) {
                markBtn.style.display = 'none';
            }
            
            // Update global counter
            const badge = document.querySelector('.badge-counter');
            if (badge) {
                const count = parseInt(badge.textContent) - 1;
                if (count > 0) {
                    badge.textContent = count;
                } else {
                    badge.remove();
                }
            }
            
            // Update Alpine store if available
            if (window.Alpine && Alpine.store('global')) {
                const store = Alpine.store('global');
                if (store.notificationsCount > 0) {
                    store.notificationsCount--;
                }
            }
        }
    } catch (error) {
        console.error('Failed to mark notification as read:', error);
    }
}

async function deleteNotification(id) {
    if (!confirm('{{ __("notifications.confirm_delete") }}')) {
        return;
    }
    
    try {
        const response = await fetch(`/notifications/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        
        if (response.ok) {
            const card = document.getElementById(`notification-${id}`);
            card.style.transform = 'translateX(100%)';
            card.style.opacity = '0';
            card.style.transition = 'all 0.3s ease';
            
            setTimeout(() => {
                card.remove();
                
                // Check if list is empty
                const remaining = document.querySelectorAll('.notification-card');
                if (remaining.length === 0) {
                    location.reload();
                }
            }, 300);
        }
    } catch (error) {
        console.error('Failed to delete notification:', error);
    }
}

async function markAllAsRead() {
    try {
        const response = await fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        
        if (response.ok) {
            // Remove all unread states
            document.querySelectorAll('.notification-card.unread').forEach(card => {
                card.classList.remove('unread');
                const iconWrapper = card.querySelector('.notification-icon-wrapper');
                if (iconWrapper) iconWrapper.classList.remove('unread-dot');
                const markBtn = card.querySelector('.btn-notification-secondary');
                if (markBtn) markBtn.style.display = 'none';
            });
            
            // Update global counter
            const badge = document.querySelector('.badge-counter');
            if (badge) badge.remove();
            
            // Update Alpine store
            if (window.Alpine && Alpine.store('global')) {
                Alpine.store('global').notificationsCount = 0;
            }
        }
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    }
}

async function deleteAll() {
    if (!confirm('{{ __("notifications.confirm_delete_all") }}')) {
        return;
    }
    
    try {
        const response = await fetch('/notifications', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        
        if (response.ok) {
            location.reload();
        }
    } catch (error) {
        console.error('Failed to delete all notifications:', error);
    }
}
</script>
@endsection
