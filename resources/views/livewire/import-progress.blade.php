<div wire:poll.2000ms="refreshState">
    <div class="space-y-2">
        <div class="flex items-center justify-between text-sm text-gray-300">
            <div>Status: <span class="font-medium text-white">{{ ucfirst($status) }}</span></div>
            <div>{{ $processed }} / {{ $total }} processed</div>
        </div>

        <div class="w-full bg-gray-700 rounded h-3 overflow-hidden">
            @php
                $percent = $total > 0 ? (int) min(100, ($processed / $total) * 100) : 0;
            @endphp
            <div class="h-3 bg-primary-600" style="width: {{ $percent }}%"></div>
        </div>

        <div class="text-xs text-gray-400">Failed: {{ $failed }}</div>
    </div>
</div>
