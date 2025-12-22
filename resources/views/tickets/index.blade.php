@extends('layouts.app')

@section('title', __('tickets.my_tickets'))

@push('styles')
    @vite('resources/css/tickets/tickets.css')
@endpush

@section('content')
<div class="tickets-page">
    <div class="tickets-container">
        <!-- Header -->
        <div class="tickets-header">
            <h1 class="tickets-title">{{ __('tickets.title') }}</h1>
            <a href="{{ route('tickets.create') }}" class="btn-create-ticket">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                {{ __('tickets.new_ticket') }}
            </a>
        </div>

        <!-- Stats -->
        <div class="ticket-stats">
            <div class="stat-card">
                <div class="stat-label">{{ __('tickets.total_tickets') }}</div>
                <div class="stat-value">{{ $tickets->total() }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">{{ __('tickets.open') }}</div>
                <div class="stat-value">{{ auth()->user()->tickets()->where('status', 'open')->count() }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">{{ __('tickets.in_progress') }}</div>
                <div class="stat-value">{{ auth()->user()->tickets()->where('status', 'in_progress')->count() }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">{{ __('tickets.resolved') }}</div>
                <div class="stat-value">{{ auth()->user()->tickets()->where('status', 'resolved')->count() }}</div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="tickets-alert success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Tickets List -->
        @if($tickets->count() > 0)
            <div class="tickets-list">
                @foreach($tickets as $ticket)
                    <a href="{{ route('tickets.show', $ticket) }}" class="ticket-card">
                        <div class="ticket-card-header">
                            <div class="ticket-card-info">
                                <div class="ticket-id">#{{ $ticket->id }}</div>
                                <h3 class="ticket-subject">{{ $ticket->subject }}</h3>
                                <p class="ticket-description">{{ Str::limit($ticket->description, 150) }}</p>
                            </div>
                            <div class="ticket-badges">
                                <span class="badge badge-status-{{ $ticket->status }}">
                                    {{ __('tickets.status_' . $ticket->status) }}
                                </span>
                                <span class="badge badge-priority-{{ $ticket->priority }}">
                                    {{ __('tickets.priority_' . $ticket->priority) }}
                                </span>
                                @if($ticket->unread_messages_for_user_count > 0)
                                    <span class="badge badge-unread">
                                        {{ __('tickets.new_messages', ['count' => $ticket->unread_messages_for_user_count]) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="ticket-card-footer">
                            <div class="ticket-meta">
                                <span class="ticket-meta-item">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                    {{ trans_choice('tickets.replies_count', $ticket->messages_count, ['count' => $ticket->messages_count]) }}
                                </span>
                                <span class="ticket-meta-item">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    {{ $ticket->created_at->diffForHumans() }}
                                </span>
                            </div>
                            @if($ticket->unread_messages_for_user_count > 0)
                                <span class="unread-indicator"></span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pagination">
                {{ $tickets->links() }}
            </div>
        @else
            <div class="empty-state">
                <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
                <h2 class="empty-state-title">{{ __('tickets.no_tickets') }}</h2>
                <p class="empty-state-description">{{ __('tickets.no_tickets_text') }}</p>
                <a href="{{ route('tickets.create') }}" class="btn-create-ticket">{{ __('tickets.create_first') }}</a>
            </div>
        @endif
    </div>
</div>
@endsection