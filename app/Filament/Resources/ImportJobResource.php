<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImportJobResource\Pages;
use App\Jobs\ImportProductsJob;
use App\Models\ImportJob;
use App\Models\ImportJob as ImportJobModel;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ImportJobResource extends Resource
{
    protected static ?string $model = ImportJob::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Imports';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('file_path')->label('File')->wrap()->limit(40),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'processing',
                        'success' => 'completed',
                        'danger' => 'failed',
                    ])
                    ->sortable(),
                TextColumn::make('total_rows')->label('Total')->sortable(),
                TextColumn::make('processed_rows')->label('Processed')->sortable(),
                TextColumn::make('failed_count')->label('Failed')->sortable(),
                TextColumn::make('started_at')->label('Started')->dateTime()->sortable(),
                TextColumn::make('finished_at')->label('Finished')->dateTime()->sortable(),
                TextColumn::make('created_at')->label('Created')->dateTime()->sortable(),
            ])
            ->actions([
                Action::make('download_failed')
                    ->label('Download failed')
                    ->icon('heroicon-m-download')
                    ->url(fn ($record) => route('admin.imports.download_failed', $record->id))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => ! empty($record->failed_file_path)),
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (ImportJobModel $record) {
                        if (in_array($record->status, ['pending', 'processing'])) {
                            $record->update(['status' => 'cancelled', 'updated_at' => now()]);
                        }
                    })
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'processing'])),
                Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-m-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (ImportJobModel $record) {
                        if (in_array($record->status, ['failed', 'cancelled'])) {
                            $record->update([
                                'status' => 'pending',
                                'processed_rows' => 0,
                                'failed_count' => 0,
                                'started_at' => null,
                                'finished_at' => null,
                                'updated_at' => now(),
                            ]);
                            ImportProductsJob::dispatch($record->file_path, $record->id)->onQueue('imports');
                        }
                    })
                    ->visible(fn ($record) => in_array($record->status, ['failed', 'cancelled'])),
                Action::make('re_run')
                    ->label('Re-run as new import')
                    ->icon('heroicon-m-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (ImportJobModel $record) {
                        $new = ImportJobModel::create([
                            'uuid' => (string) \Illuminate\Support\Str::uuid(),
                            'user_id' => $record->user_id,
                            'file_path' => $record->file_path,
                            'mapping' => $record->mapping,
                            'status' => 'pending',
                        ]);
                        ImportProductsJob::dispatch($new->file_path, $new->id)->onQueue('imports');
                    })
                    ->visible(fn ($record) => in_array($record->status, ['completed', 'failed', 'cancelled'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImportJobs::route('/'),
            'view' => Pages\ViewImportJob::route('/{record}'),
            'configure' => Pages\ConfigureImport::route('/{record}/configure'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
