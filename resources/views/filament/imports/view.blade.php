@php
/** @var \Illuminate\Database\Eloquent\Model $record */
/** @var array $previewRows */
@endphp

<x-filament::page>
    <div class="space-y-4">
        <div class="bg-gray-900/50 p-4 rounded">
            <h2 class="text-xl font-semibold text-white">Import #{{ $record->id }} — {{ ucfirst($record->status) }}</h2>
            <p class="text-sm text-gray-300">File: <span class="font-medium">{{ $record->file_path }}</span></p>
            <div class="mt-3 flex flex-wrap gap-3 text-sm text-gray-200">
                <div>Total: <span class="font-medium">{{ $record->total_rows }}</span></div>
                <div>Processed: <span class="font-medium">{{ $record->processed_rows }}</span></div>
                <div>Failed: <span class="font-medium">{{ $record->failed_count }}</span></div>
                <div>Started: <span class="font-medium">{{ optional($record->started_at)->format('M d, Y H:i:s') ?? '-' }}</span></div>
                <div>Finished: <span class="font-medium">{{ optional($record->finished_at)->format('M d, Y H:i:s') ?? '-' }}</span></div>
                <div>Created: <span class="font-medium">{{ optional($record->created_at)->format('M d, Y H:i:s') ?? '-' }}</span></div>
            </div>

            @if ($record->failed_file_path)
                <div class="mt-4">
                    <a href="{{ route('admin.imports.download_failed', $record->id) }}" target="_blank" class="filament-button inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded">Download failed CSV</a>
                </div>
            @endif
        </div>

        <div>
            @if($record->status !== 'pending')
                @livewire('import-progress', ['importId' => $record->id])
            @endif
        </div>

        <div class="bg-gray-900/40 p-4 rounded">
            <h3 class="text-md font-semibold text-white">Failed rows preview (up to 20 rows)</h3>
            @if (empty($previewRows))
                <p class="text-sm text-gray-300 mt-2">No failed rows to preview.</p>
            @else
                <div class="overflow-auto mt-3">
                    <table class="min-w-full table-auto text-sm text-gray-100">
                        <thead>
                            <tr class="text-left border-b border-gray-700">
                                @foreach(array_keys((array)$previewRows[0]) as $col)
                                    <th class="px-3 py-2 font-medium">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewRows as $row)
                                <tr class="border-b border-gray-800">
                                    @foreach((array)$row as $cell)
                                        <td class="px-3 py-2 align-top">{{ is_array($cell) || is_object($cell) ? json_encode($cell, JSON_UNESCAPED_UNICODE) : $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-filament::page>
