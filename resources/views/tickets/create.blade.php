@extends('layouts.app')

@section('title', __('tickets.create_ticket'))

@push('styles')
    @vite('resources/css/tickets/tickets.css')
@endpush

@section('content')
<div class="tickets-page">
    <div class="tickets-container">
        <a href="{{ route('tickets.index') }}" class="btn-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            {{ __('tickets.back_to_tickets') }}
        </a>
        
        <div class="create-ticket-form">
            <div class="form-header">
                <h1 class="form-title">{{ __('tickets.create_title') }}</h1>
                <p class="form-subtitle">{{ __('tickets.create_subtitle') }}</p>
            </div>

            <!-- Language Notice -->
            <div class="language-notice">
                <div class="language-notice-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                    </svg>
                </div>
                <div class="language-notice-content">
                    <strong>{{ __('tickets.language_notice_title') }}</strong>
                    <p>{{ __('tickets.language_notice_text') }}</p>
                </div>
            </div>
            
            <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label class="form-label" for="subject">{{ __('tickets.subject') }}</label>
                    <input type="text" 
                           id="subject" 
                           name="subject" 
                           class="form-input" 
                           value="{{ old('subject') }}" 
                           placeholder="{{ __('tickets.subject_placeholder') }}"
                           required>
                    @error('subject')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="priority">{{ __('tickets.priority') }}</label>
                    <select id="priority" name="priority" class="form-select" required>
                        <option value="">{{ __('tickets.select_priority') }}</option>
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>{{ __('tickets.priority_low') }}</option>
                        <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }} selected>{{ __('tickets.priority_medium') }}</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>{{ __('tickets.priority_high') }}</option>
                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>{{ __('tickets.priority_urgent') }}</option>
                    </select>
                    @error('priority')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">{{ __('tickets.description') }}</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-textarea" 
                              rows="6" 
                              placeholder="{{ __('tickets.description_placeholder') }}"
                              required>{{ old('description') }}</textarea>
                    @error('description')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('tickets.attachments_optional') }}</label>
                    <label class="file-upload">
                        <input type="file" name="attachments[]" multiple class="sr-only" accept="image/*,.pdf,.doc,.docx,.txt">
                        <svg class="file-upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="file-upload-text">{{ __('tickets.click_upload') }}</p>
                        <p class="file-upload-hint">{{ __('tickets.upload_hint') }}</p>
                    </label>
                    @error('attachments.*')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
                
                <button type="submit" class="btn-submit">
                    {{ __('tickets.create_ticket_btn') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection