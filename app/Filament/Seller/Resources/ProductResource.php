<?php

namespace App\Filament\Seller\Resources;

use App\Filament\Seller\Resources\ProductResource\Pages;
use App\Filament\Seller\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Products';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'My Products';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $companyId = $user?->company?->id;

        return parent::getEloquentQuery()
            ->where('company_id', $companyId);
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        return $user && $user->company !== null;
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Product Name')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, ?Product $record) {
                                if (! $record) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->label('Slug')
                            ->rules(['alpha_dash']),

                        Textarea::make('description')
                            ->maxLength(65535)
                            ->label('Short Description')
                            ->rows(3),

                        Textarea::make('long_description')
                            ->maxLength(65535)
                            ->label('Long Description')
                            ->rows(5),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pricing & Inventory')
                    ->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$')
                            ->label('Regular Price'),

                        TextInput::make('sale_price')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$')
                            ->label('Sale Price'),

                        TextInput::make('stock_quantity')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->label('Stock Quantity'),

                        TextInput::make('sku')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->label('SKU'),

                        TextInput::make('weight')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('kg')
                            ->label('Weight'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Categories & Settings')
                    ->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->preload()
                            ->searchable()

                            ->label('Category'),

                        Toggle::make('is_featured')
                            ->label('Featured Product'),

                        Toggle::make('is_active')
                            ->default(true)
                            ->label('Active'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Product Images')
                    ->schema([
                        Repeater::make('images')
                            ->relationship('images')
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Image')
                                    ->image()
                                    ->directory('products')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->required(),

                                TextInput::make('alt_text')
                                    ->label('Alt Text')
                                    ->maxLength(255)
                                    ->placeholder('Describe this image'),

                                Toggle::make('is_primary')
                                    ->label('Primary Image')
                                    ->helperText('Only one image can be primary'),

                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Sort Order'),
                            ])
                            ->columns(2)
                            ->reorderable('sort_order')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['alt_text'] ?? 'Image')
                            ->addActionLabel('Add Image')
                            ->defaultItems(0),
                    ]),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),

                Forms\Components\Hidden::make('company_id')
                    ->default(fn () => Auth::user()?->company?->id),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ImageColumn::make('primary_image')
                    ->label('Image')
                    ->getStateUsing(function (Product $record) {
                        $primaryImage = $record->getPrimaryImage();
                        if (! $primaryImage || ! $primaryImage->image_path) {
                            return null;
                        }

                        return asset('storage/'.$primaryImage->image_path);
                    })
                    ->size(60)
                    ->circular()
                    ->extraImgAttributes(['loading' => 'lazy']),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Sale Price')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state > 10 => 'success',
                        $state > 0 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),

                Tables\Filters\Filter::make('low_stock')
                    ->query(fn ($query) => $query->where('stock_quantity', '<=', 10))
                    ->label('Low Stock'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
