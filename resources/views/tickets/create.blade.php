@extends('layouts.app')

@section('title', 'Create Support Ticket')

@push('styles')
    @vite('resources/css/tickets/tickets.css')
    <style>
        .create-ticket-form {
            max-width: 800px;
            margin: 0 auto;
            background: var(--ticket-card);
            border: 1px solid var(--ticket-border);
            border-radius: 12px;
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .form-label .required {
            color: #ef4444;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #0a0a0a;
            border: 1px solid var(--ticket-border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.9375rem;
            transition: all 0.2s ease;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 150px;
            font-family: inherit;
            line-height: 1.6;
        }

        .form-help {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            margin-top: 0.375rem;
        }

        .form-error {
            font-size: 0.8125rem;
            color: #ef4444;
            margin-top: 0.375rem;
        }

        .file-upload-area {
            border: 2px dashed var(--ticket-border);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: var(--accent);
            background: rgba(245, 158, 11, 0.05);
        }

        .file-upload-area.dragover {
            border-color: var(--accent);
            background: rgba(245, 158, 11, 0.1);
        }

        .file-upload-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 1rem;
            color: var(--text-secondary);
        }

        .file-upload-text {
            font-size: 0.9375rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .file-upload-hint {
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }

        .file-list {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            background: #0a0a0a;
            border: 1px solid var(--ticket-border);
            border-radius: 8px;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .file-icon {
            width: 32px;
            height: 32px;
            color: var(--accent);
        }

        .file-name {
            font-size: 0.875rem;
            color: var(--text-primary);
            font-weight: 500;
        }

        .file-size {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .file-remove {
            background: transparent;
            border: none;
            color: #ef4444;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .file-remove:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--ticket-border);
        }

        .btn-submit {
            flex: 1;
            padding: 0.875rem 1.5rem;
            background: var(--accent);
            color: #0a0a0a;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            background: #d97706;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .btn-cancel {
            padding: 0.875rem 1.5rem;
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--ticket-border);
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-block;
            text-align: center;
        }

        .btn-cancel:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #ef4444;
        }
    </style>
@endpush

@section('content')
<div class="tickets-page">
    <div class="tickets-container">
        <!-- Header -->
        <div class="tickets-header">
            <h1 class="tickets-title">Create New Ticket</h1>
        </div>

        <!-- Form -->
        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="create-ticket-form">
            @csrf

            @if($errors->any())
                <div class="alert-error">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin: 0.5rem 0 0; padding-left: 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Subject -->
            <div class="form-group">
                <label class="form-label">
                    Subject <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="subject" 
                    class="form-input" 
                    placeholder="Brief description of your issue"
                    value="{{ old('subject') }}"
                    required
                >
                @error('subject')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Priority -->
            <div class="form-group">
                <label class="form-label">
                    Priority <span class="required">*</span>
                </label>
                <select name="priority" class="form-select" required>
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low - General inquiry</option>
                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium - Normal issue</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High - Urgent issue</option>
                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent - Critical problem</option>
                </select>
                <div class="form-help">Select the priority level that best describes your issue</div>
                @error('priority')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label">
                    Description <span class="required">*</span>
                </label>
                <textarea 
                    name="description" 
                    class="form-textarea" 
                    placeholder="Please provide detailed information about your issue..."
                    required
                >{{ old('description') }}</textarea>
                <div class="form-help">Minimum 10 characters. Include as much detail as possible.</div>
                @error('description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- File Upload -->
            <div class="form-group">
                <label class="form-label">
                    Attachments (Optional)
                </label>
                <div class="file-upload-area" id="fileUploadArea">
                    <svg class="file-upload-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <div class="file-upload-text">Click to upload or drag and drop</div>
                    <div class="file-upload-hint">Images, PDF, Word documents up to 10MB each (max 5 files)</div>
                    <input 
                        type="file" 
                        name="attachments[]" 
                        id="fileInput" 
                        multiple 
                        accept="image/*,.pdf,.doc,.docx,.txt"
                        style="display: none;"
                    >
                </div>
                <div class="file-list" id="fileList"></div>
                @error('attachments.*')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    Create Ticket
                </button>
                <a href="{{ route('tickets.index') }}" class="btn-cancel">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    let selectedFiles = [];

    // Click to upload
    fileUploadArea.addEventListener('click', () => fileInput.click());

    // Drag and drop
    fileUploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileUploadArea.classList.add('dragover');
    });

    fileUploadArea.addEventListener('dragleave', () => {
        fileUploadArea.classList.remove('dragover');
    });

    fileUploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        fileUploadArea.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        const dt = new DataTransfer();
        
        // Add existing files
        selectedFiles.forEach(file => dt.items.add(file));
        
        // Add new files (max 5 total)
        for (let i = 0; i < files.length && selectedFiles.length < 5; i++) {
            const file = files[i];
            if (file.size <= 10 * 1024 * 1024) { // 10MB max
                selectedFiles.push(file);
                dt.items.add(file);
            }
        }

        fileInput.files = dt.files;
        renderFileList();
    }

    function renderFileList() {
        fileList.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <div class="file-info">
                    <svg class="file-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div>
                        <div class="file-name">${file.name}</div>
                        <div class="file-size">${formatFileSize(file.size)}</div>
                    </div>
                </div>
                <button type="button" class="file-remove" onclick="removeFile(${index})">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            `;
            fileList.appendChild(fileItem);
        });
    }

    window.removeFile = function(index) {
        selectedFiles.splice(index, 1);
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
        renderFileList();
    };

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
});
</script>
@endsection