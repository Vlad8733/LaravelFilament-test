<?php

namespace App\Filament\Resources\ImportJobResource\Pages;

use App\Filament\Resources\ImportJobResource;
use App\Models\ImportJob;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ConfigureImport extends Page
{
    use InteractsWithForms;

    protected static string $resource = ImportJobResource::class;

    protected static string $view = 'filament.imports.configure';

    public ImportJob $importJob;

    public array $headers = [];

    public array $mapping = [];

    protected function getFormSchema(): array
    {
        // target fields we support mapping to
        $targetFields = [
            'name' => 'Product Name',
            'slug' => 'Slug',
            'sku' => 'SKU',
            'price' => 'Price',
            'sale_price' => 'Sale Price',
            'stock_quantity' => 'Stock Quantity',
            'category' => 'Category',
            'images' => 'Images (URLs, ; delimited)',
            'variant_sku' => 'Variant SKU',
            'variant_price' => 'Variant Price',
            'variant_attributes' => 'Variant Attributes',
        ];

        $options = array_combine($this->headers ?: ['none'], $this->headers ?: ['none']);

        $fields = [];
        foreach ($targetFields as $key => $label) {
            $fields[] = Select::make('mapping.'.$key)
                ->label($label)
                ->options(array_merge(['' => '— none —'], $options))
                ->default(fn () => $this->mapping[$key] ?? '');
        }

        return [Card::make()->schema($fields)];
    }

    public function mount($record): void
    {
        $this->importJob = ImportJob::findOrFail($record);

        $path = $this->importJob->file_path;
        if ($path && Storage::disk('local')->exists($path)) {
            $full = Storage::disk('local')->path($path);
            if (file_exists($full)) {
                $handle = fopen($full, 'r');
                $header = fgetcsv($handle) ?: [];
                fclose($handle);
                $this->headers = $header;
            }
        }

        // preload mapping if present
        $this->mapping = $this->importJob->mapping ?? [];

        // fill the form state from mapping
        $this->form->fill(['mapping' => $this->mapping]);
    }

    public function submit()
    {
        $data = $this->form->getState();
        // persist mapping
        $this->importJob->update(['mapping' => $data]);

        // dispatch job now that mapping is configured
        \App\Jobs\ImportProductsJob::dispatch($this->importJob->file_path, $this->importJob->id)->onQueue('imports');

        Notification::make()->success()->title('Import queued with mapping')->send();

        return redirect(ImportJobResource::getUrl('view', ['record' => $this->importJob->id]));
    }
}
