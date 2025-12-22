<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ImportJob;

class ImportProgress extends Component
{
    public int $importId;
    public int $total = 0;
    public int $processed = 0;
    public int $failed = 0;
    public string $status = 'pending';

    public function mount(int $importId)
    {
        $this->importId = $importId;
        $this->refreshState();
    }

    public function refreshState(): void
    {
        $import = ImportJob::find($this->importId);
        if (!$import) {
            return;
        }

        $this->total = (int) ($import->total_rows ?? 0);
        $this->processed = (int) ($import->processed_rows ?? 0);
        $this->failed = (int) ($import->failed_count ?? 0);
        $this->status = $import->status ?? 'pending';
    }

    public function render()
    {
        // polled from the blade via wire:poll
        $this->refreshState();
        return view('livewire.import-progress');
    }
}
