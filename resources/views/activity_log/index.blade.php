@extends('layouts.app')

@section('title', __('activity_log.title'))

@push('styles')
    @vite('resources/css/activity-log/acivity-log.css')
@endpush

@section('content')
<div class="activity-log-page">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">{{ __('wishlist.home') }}</a>
            <span>/</span>
            <span>{{ __('activity_log.title') }}</span>
        </nav>

        <!-- Page Header -->
        <div class="page-header">
            <h1>{{ __('activity_log.title') }}</h1>
            <span class="count">{{ $logs->total() }} {{ trans_choice('activity_log.records', $logs->total()) }}</span>
        </div>

        <!-- Filters -->
        <form method="GET" class="filters-card">
            <div class="filter-group">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="{{ __('activity_log.placeholder_search') }}"
                    class="filter-input"
                >
            </div>
            <div class="filter-group">
                <select name="type" onchange="this.form.submit()" class="filter-select">
                    <option value="">{{ __('activity_log.alltypes') }}</option>
                    @foreach($types ?? [] as $type)
                        <option value="{{ $type['key'] }}" @selected(request('type') === $type['key'])>{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="filter-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                {{ __('activity_log.search') }}
            </button>
            @if(request('search') || request('type'))
                <a href="{{ route('activity_log.index') }}" class="filter-reset">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    {{ __('activity_log.reset') }}
                </a>
            @endif
        </form>

        <!-- Table Card -->
        <div class="table-card">
            @if($logs->count() > 0)
                <div class="table-wrapper">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>{{ __('activity_log.action') }}</th>
                                <th>{{ __('activity_log.ip') }}</th>
                                <th>{{ __('activity_log.user_agent') }}</th>
                                <th>{{ __('activity_log.date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td class="action-cell">
                                        @php
                                            $displayAction = Str::contains($log->action, ':') 
                                                ? trim(Str::after($log->action, ':')) 
                                                : $log->action;
                                        @endphp
                                        {{ $displayAction }}
                                    </td>
                                    <td class="ip-cell">
                                        <code>{{ $log->ip_address }}</code>
                                    </td>
                                    <td class="ua-cell">
                                        {{ $log->user_agent }}
                                    </td>
                                    <td class="date-cell">
                                        <time datetime="{{ $log->created_at->toIso8601String() }}">
                                            <span class="date">{{ $log->created_at->format('d.m.Y') }}</span>
                                            <span class="time">{{ $log->created_at->format('H:i') }}</span>
                                        </time>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="pagination-wrapper">
                        {{ $logs->links() }}
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <h2>{{ __('activity_log.empty') }}</h2>
                    <p>{{ __('activity_log.empty_description') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
