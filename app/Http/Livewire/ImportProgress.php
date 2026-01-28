<?php

namespace App\Http\Livewire;

use App\Models\ImportJob;
use Livewire\Component;

class ImportProgress extends Component
{
    public int $importId;

    public int $total = 0;

    public int $processed = 0;

    public int $failed = 0;

    public string $status = 'pending';

    public function mount(int $id)
    {
        $this->importId = $id;
        $this->refreshState();
    }

    public function refreshState(): void
    {
        $i = ImportJob::find($this->importId);
        if (! $i) {
            return;
        }
        $this->total = (int) ($i->total_rows ?? 0);
        $this->processed = (int) ($i->processed_rows ?? 0);
        $this->failed = (int) ($i->failed_count ?? 0);
        $this->status = $i->status ?? 'pending';
    }

    public function render()
    {
        $this->refreshState();

        return view('livewire.import-progress');
    }
}
