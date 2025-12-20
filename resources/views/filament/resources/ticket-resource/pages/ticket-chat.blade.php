<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Ticket Info --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-2">{{ $record->subject }}</h2>
            <div class="flex gap-4 text-sm text-gray-600 dark:text-gray-400">
                <span>Status: <strong>{{ ucfirst($record->status) }}</strong></span>
                <span>Priority: <strong>{{ ucfirst($record->priority) }}</strong></span>
                <span>Created: {{ $record->created_at->diffForHumans() }}</span>
            </div>
        </div>

        {{-- Messages --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-h-[600px] overflow-y-auto space-y-4">
            @foreach($record->messages as $message)
                <div class="flex {{ $message->is_admin_reply ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[70%] {{ $message->is_admin_reply ? 'bg-blue-100 dark:bg-blue-900' : 'bg-gray-100 dark:bg-gray-700' }} rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <strong class="text-sm">{{ $message->user->name }}</strong>
                            <span class="text-xs text-gray-500">{{ $message->created_at->format('M d, H:i') }}</span>
                        </div>
                        <p class="text-sm whitespace-pre-wrap">{{ $message->message }}</p>
                        
                        @if($message->attachments->count() > 0)
                            <div class="mt-2 space-y-1">
                                @foreach($message->attachments as $attachment)
                                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="text-xs text-blue-600 hover:underline block">
                                        📎 {{ $attachment->file_name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Reply Form --}}
        <form wire:submit="sendMessage" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="space-y-4">
                <textarea 
                    wire:model="newMessage" 
                    rows="4" 
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                    placeholder="Type your reply..."
                ></textarea>
                
                <x-filament::button type="submit" color="success">
                    Send Reply
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
