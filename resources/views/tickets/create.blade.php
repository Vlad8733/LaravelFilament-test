@extends('layouts.app')

@section('title', __('tickets.create_ticket'))

@push('styles')
    @vite('resources/css/tickets/tickets.css')
    <style>
        .create-ticket-form {
            max-width: 700px;
            margin: 0 auto;
            background: linear-gradient(180deg, #1a1a1a, #141414);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 16px;
            padding: 32px;
        }
        
        .form-header {
            margin-bottom: 32px;
        }
        
        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e5e7eb;
            margin-bottom: 8px;
        }
        
        .form-subtitle {
            color: #9ca3af;
            font-size: 0.9375rem;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #e5e7eb;
            margin-bottom: 8px;
        }
        
        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0,0,0,0.3) !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
            border-radius: 10px !important;
            color: #e5e7eb !important;
            font-size: 0.9375rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1) !important;
        }
        
        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 20px !important;
            padding-right: 44px;
        }
        
        .form-textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .file-upload {
            display: block !important;
            border: 2px dashed rgba(255,255,255,0.15) !important;
            border-radius: 12px !important;
            padding: 32px 24px !important;
            text-align: center !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            background: rgba(0,0,0,0.2) !important;
        }
        
        .file-upload:hover {
            border-color: rgba(245, 158, 11, 0.5) !important;
            background: rgba(245, 158, 11, 0.05) !important;
        }
        
        .file-upload-icon {
            width: 48px !important;
            height: 48px !important;
            margin: 0 auto 12px !important;
            color: #6b7280 !important;
            display: block !important;
        }
        
        .file-upload-text {
            color: #9ca3af !important;
            font-size: 0.9375rem !important;
            margin: 0 0 4px 0 !important;
        }
        
        .file-upload-hint {
            color: #6b7280 !important;
            font-size: 0.8125rem !important;
            margin: 0 !important;
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            color: #000 !important;
            font-weight: 600;
            font-size: 1rem;
            border: none !important;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #9ca3af;
            text-decoration: none;
            font-size: 0.875rem;
            margin-bottom: 24px;
            transition: color 0.2s;
        }
        
        .btn-back:hover {
            color: #f59e0b;
        }
        
        .btn-back svg {
            width: 20px !important;
            height: 20px !important;
        }
        
        .error-message {
            color: #ef4444;
            font-size: 0.8125rem;
            margin-top: 6px;
        }
    </style>
@endpush

@section('content')
<div class="tickets-page">
    <div class="tickets-container" style="padding-top: 32px;">
        <a href="{{ route('tickets.index') }}" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            {{ __('tickets.back_to_tickets') }}
        </a>
        
        <div class="create-ticket-form">
            <div class="form-header">
                <h1 class="form-title">{{ __('tickets.create_title') }}</h1>
                <p class="form-subtitle">{{ __('tickets.create_subtitle') }}</p>
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
                        <input type="file" name="attachments[]" multiple style="display: none;" accept="image/*,.pdf,.doc,.docx,.txt">
                        <svg class="file-upload-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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