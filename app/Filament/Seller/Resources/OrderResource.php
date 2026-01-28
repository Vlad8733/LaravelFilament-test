<?php

namespace App\Filament\Seller\Resources;

use App\Filament\Seller\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\OrderStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Customer Orders';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $companyId = $user?->company?->id;

        return parent::getEloquentQuery()
            ->whereHas('items.product', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->disabled(),

                        Forms\Components\Select::make('order_status_id')
                            ->label('Order Status')
                            ->options(OrderStatus::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('tracking_number')
                            ->maxLength(255)
                            ->placeholder('Enter tracking number'),

                        Forms\Components\TextInput::make('customer_name')
                            ->disabled(),

                        Forms\Components\TextInput::make('customer_email')
                            ->disabled()
                            ->formatStateUsing(fn (?string $state) => mask_email($state)),

                        Forms\Components\Textarea::make('shipping_address')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('total')
                            ->prefix('$')
                            ->disabled(),

                        Forms\Components\TextInput::make('payment_status')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status.name')
                    ->badge()
                    ->color(fn ($record) => $record->status?->color ?? 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tracking_number')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Tracking number copied!')
                    ->placeholder('No tracking'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_status_id')
                    ->relationship('status', 'name')
                    ->label('Status'),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('change_status')
                    ->label('Change Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('order_status_id')
                            ->label('New Status')
                            ->options(OrderStatus::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Tracking Number')
                            ->placeholder('Add tracking number (optional)'),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->placeholder('Add notes about this status change...'),
                    ])
                    ->action(function (array $data, Order $record): void {

                        if (! empty($data['tracking_number'])) {
                            $record->update(['tracking_number' => $data['tracking_number']]);
                        }

                        $record->updateStatus(
                            $data['order_status_id'],
                            $data['notes'] ?? null,
                            auth()->id()
                        );

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Status Updated')
                            ->body('Order status has been changed successfully.')
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_change_status')
                    ->label('Change Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('order_status_id')
                            ->label('New Status')
                            ->options(OrderStatus::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->rows(2)
                            ->placeholder('Notes for all orders...'),
                    ])
                    ->action(function (array $data, $records): void {
                        foreach ($records as $record) {
                            $record->updateStatus(
                                $data['order_status_id'],
                                $data['notes'] ?? null,
                                auth()->id()
                            );
                        }

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Statuses Updated')
                            ->body(count($records).' orders have been updated.')
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
