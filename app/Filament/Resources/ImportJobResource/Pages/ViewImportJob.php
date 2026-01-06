<?php

namespace App\Filament\Resources\ImportJobResource\Pages;

use App\Filament\Resources\ImportJobResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewImportJob extends ViewRecord
{
    protected static string $resource = ImportJobResource::class;

    /**
     * Use custom blade view stored at resources/views/filament/imports/view.blade.php
     *
     * @var view-string
     */
    protected static string $view = 'filament.imports.view';

    public array $previewRows = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    public function mount($record): void
    {
        parent::mount($record);

        $this->loadPreview();
    }

    protected function loadPreview(): void
    {
        $path = $this->record->failed_file_path;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            $this->previewRows = [];

            return;
        }

        $full = Storage::disk('local')->path($path);
        if (! file_exists($full)) {
            $this->previewRows = [];

            return;
        }

        $handle = fopen($full, 'r');
        if ($handle === false) {
            $this->previewRows = [];

            return;
        }

        $header = fgetcsv($handle) ?: [];
        $rows = [];
        $limit = 20;
        $i = 0;
        while (($row = fgetcsv($handle)) !== false && $i < $limit) {
            $rows[] = array_combine($header, $row) ?: $row;
            $i++;
        }
        fclose($handle);

        $this->previewRows = $rows;
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'previewRows' => $this->previewRows,
            'record' => $this->record,
        ]);
    }
}
