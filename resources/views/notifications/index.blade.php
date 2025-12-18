@extends('layouts.app')

@section('title', 'Notifications')

@push('styles')
    @vite('resources/css/tickets/tickets.css')
    <style>
        .notifications-page {
            min-height: 100vh;
            background: var(--ticket-bg);
            padding: 2rem 0;
        }

        .notifications-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .notifications-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .notifications-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-action-secondary {
            padding: 0.5rem 1rem;
            background: rgba(156, 163, 175, 0.15);
            color: #9ca3af;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-action-secondary:hover {
            background: rgba(156, 163, 175, 0.25);
        }

        .notification-card {
            background: var(--ticket-card);
            border: 1px solid var(--ticket-border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }

        .notification-card:hover {
            background: var(--ticket-hover);
            border-color: rgba(255, 255, 255, 0.12);
        }

        .notification-card.unread {
            border-left: 3px solid var(--accent);
            background: rgba(245, 158, 11, 0.05);
        }

        .notification-card-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .notification-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: rgba(245, 158, 11, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-card-content {
            flex: 1;
        }

        .notification-card-message {
            font-size: 1rem;
            color: var(--text-primary);
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        .notification-card-time {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .notification-card-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn-notification {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
            display: inline-block;
        }

        .btn-notification-primary {
            background: var(--accent);
            color: #0a0a0a;
        }

        .btn-notification-primary:hover {
            background: #d97706;
        }

        .btn-notification-ghost {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--ticket-border);
        }

        .btn-notification-ghost:hover {
            border-color: var(--accent);
            color: var(--accent);
        }
    </style>
@endpush

@section('content')
<div class="notifications-page">
    <div class="notifications-container">
        <div class="notifications-header">
            <h1 class="notifications-title">Notifications</h1>
            <div class="notifications-actions">
                @if($notifications->total() > 0)
                    <button onclick="markAllAsRead()" class="btn-action-secondary">
                        Mark all as read
                    </button>
                    <button onclick="deleteAll()" class="btn-action-secondary">
                        Clear all
                    </button>
                @endif
            </div>
        </div>

        @if($notifications->count() > 0)
            @foreach($notifications as $notification)
                <div class="notification-card {{ $notification->read_at ? '' : 'unread' }}" id="notification-{{ $notification->id }}">
                    <div class="notification-card-header">
                        <div class="notification-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <div class="notification-card-content">
                            <div class="notification-card-message">
                                {{ $notification->data['message'] ?? 'New notification' }}
                            </div>
                            <div class="notification-card-time">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                            <div class="notification-card-actions">
                                @if(isset($notification->data['ticket_id']))
                                    <a href="{{ route('tickets.show', $notification->data['ticket_id']) }}" class="btn-notification btn-notification-primary">
                                        View Ticket
                                    </a>
                                @endif
                                @if(!$notification->read_at)
                                    <button onclick="markAsRead('{{ $notification->id }}')" class="btn-notification btn-notification-ghost">
                                        Mark as read
                                    </button>
                                @endif
                                <button onclick="deleteNotification('{{ $notification->id }}')" class="btn-notification btn-notification-ghost">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div style="margin-top: 2rem;">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="empty-state">
                <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <h2 class="empty-state-title">No Notifications</h2>
                <p class="empty-state-description">You're all caught up! Check back later for new notifications.</p>
            </div>
        @endif
    </div>
</div>

<script>
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
            const notification = document.getElementById(`notification-${id}`);
            notification.classList.remove('unread');
            
            // Обновляем счетчик
            const badge = document.querySelector('.badge-counter');
            if (badge) {
                const count = parseInt(badge.textContent) - 1;
                if (count > 0) {
                    badge.textContent = count;
                } else {
                    badge.remove();
                }
            }
        }
    } catch (error) {
        console.error('Failed to mark notification as read:', error);
    }
}

async function deleteNotification(id) {
    if (!confirm('Are you sure you want to delete this notification?')) {
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
            const notification = document.getElementById(`notification-${id}`);
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
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
            location.reload();
        }
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    }
}

async function deleteAll() {
    if (!confirm('Are you sure you want to delete all notifications?')) {
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