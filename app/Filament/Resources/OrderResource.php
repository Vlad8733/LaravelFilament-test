<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Orders & Sales';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['status', 'user'])
            ->withCount('items');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->maxLength(255),

                        Forms\Components\Select::make('order_status_id')
                            ->relationship('status', 'name')
                            ->required()
                            ->default(fn () => \App\Models\OrderStatus::pending()?->id)
                            ->native(false)
                            ->preload()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('tracking_number')
                            ->maxLength(255)
                            ->placeholder('Enter tracking number'),

                        Forms\Components\TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('customer_email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('shipping_address')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('discount_amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('total')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'fake' => 'Fake Payment',
                                'stripe' => 'Stripe',
                                'paypal' => 'PayPal',
                            ])
                            ->required(),

                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
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
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tracking_number')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Tracking number copied!')
                    ->placeholder('No tracking'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_email')
                    ->searchable()
                    ->formatStateUsing(fn (?string $state) => mask_email($state)),

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
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('order_status_id')
                    ->relationship('status', 'name')
                    ->label('Order Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
