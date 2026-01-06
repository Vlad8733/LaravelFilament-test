<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Coupon Details')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(9)
                            ->placeholder('XXXX-XXXX')
                            ->unique(ignoreRecord: true)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate')
                                    ->icon('heroicon-o-arrow-path')
                                    ->action(fn (Set $set) => $set('code', Coupon::generateCode()))
                            )
                            ->default(fn () => Coupon::generateCode()),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->required()
                                    ->options([
                                        'percentage' => 'Percentage (%)',
                                        'fixed' => 'Fixed Amount ($)',
                                    ])
                                    ->default('percentage'),

                                Forms\Components\TextInput::make('value')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix(fn (Get $get) => $get('type') === 'percentage' ? '%' : '$'),
                            ]),

                        Forms\Components\TextInput::make('minimum_amount')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('No minimum')
                            ->helperText('Minimum order amount required'),
                    ])->columns(1),

                Forms\Components\Section::make('Usage Limits')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('usage_limit')
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('Unlimited')
                                    ->helperText('Leave empty for unlimited'),

                                Forms\Components\TextInput::make('used_count')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('Times used'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('starts_at')
                                    ->required()
                                    ->default(now()),

                                Forms\Components\DateTimePicker::make('expires_at')
                                    ->required()
                                    ->after('starts_at'),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Enable or disable this coupon'),
                    ]),

                Forms\Components\Section::make('Applies To')
                    ->schema([
                        Forms\Components\Select::make('applies_to')
                            ->required()
                            ->options([
                                'all' => 'All Products',
                                'categories' => 'Specific Categories',
                                'products' => 'Specific Products',
                            ])
                            ->default('all')
                            ->live(),

                        Forms\Components\Select::make('category_ids')
                            ->multiple()
                            ->options(Category::pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (Get $get) => $get('applies_to') === 'categories')
                            ->required(fn (Get $get) => $get('applies_to') === 'categories'),

                        Forms\Components\Select::make('product_ids')
                            ->multiple()
                            ->options(Product::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (Get $get) => $get('applies_to') === 'products')
                            ->required(fn (Get $get) => $get('applies_to') === 'products'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'percentage' ? 'Percentage' : 'Fixed')
                    ->color(fn (string $state) => $state === 'percentage' ? 'info' : 'success'),

                Tables\Columns\TextColumn::make('value')
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percentage' ? "{$state}%" : "\${$state}")
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage')
                    ->state(fn ($record) => $record->usage_limit
                        ? "{$record->used_count} / {$record->usage_limit}"
                        : "{$record->used_count} / ∞"),

                Tables\Columns\TextColumn::make('applies_to')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
                        'all' => 'gray',
                        'categories' => 'warning',
                        'products' => 'info',
                    }),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->color(fn ($state) => $state < now() ? 'danger' : 'success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed',
                    ]),
                Tables\Filters\SelectFilter::make('applies_to')
                    ->options([
                        'all' => 'All Products',
                        'categories' => 'Categories',
                        'products' => 'Products',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\Action::make('copy')
                    ->icon('heroicon-o-clipboard')
                    ->action(fn ($record) => null)
                    ->extraAttributes(fn ($record) => [
                        'x-data' => '{}',
                        'x-on:click' => "navigator.clipboard.writeText('{$record->code}')",
                    ]),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
