@php
/** Page available as $this in Filament pages; avoid using undefined $page variable */
@endphp

<x-filament::page>
    <div class="max-w-3xl">
        <h2 class="text-xl font-semibold mb-4">Configure Import #{{ $this->importJob->id }}</h2>

        <form wire:submit.prevent="submit" x-data>
            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button wire:click="submit">Save & Start Import</x-filament::button>
                <a href="{{ \App\Filament\Resources\ImportJobResource::getUrl('view', ['record' => $this->importJob->id]) }}" class="ml-3 filament-button">Cancel</a>
            </div>
        </form>
    </div>
</x-filament::page>
