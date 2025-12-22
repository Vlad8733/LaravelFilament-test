<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\ActivityLogsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use Filament\Notifications\Notification;
use App\Jobs\ImportProductsJob;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                                if (!$record) {
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
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => 
                                        $set('slug', Str::slug($state))),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Category::class, 'slug'),
                                Textarea::make('description'),
                                Toggle::make('is_active')
                                    ->default(true),
                            ])
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
                        if (!$primaryImage || !$primaryImage->image_path) {
                            return null;
                        }
                        // Возвращаем полный URL
                        return asset('storage/' . $primaryImage->image_path);
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
            ->headerActions([
                \Filament\Tables\Actions\Action::make('import')
                    ->label('Import CSV')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('csv')
                            ->label('CSV file')
                            ->required()
                            ->acceptedFileTypes(['text/csv', 'text/plain'])
                            ->disk('local')
                            ->directory('imports'),
                    ])
                    ->action(function (array $data) {
                        if (!isset($data['csv'])) {
                            Notification::make()->danger()->title('No file uploaded')->send();
                            return;
                        }

                        // $data['csv'] will be the stored path when using FileUpload with disk
                        $path = $data['csv'];

                        // Create an ImportJob record so admin can configure mapping before dispatch
                        $import = \App\Models\ImportJob::create([
                            'uuid' => (string) \Illuminate\Support\Str::uuid(),
                            'user_id' => auth()->id() ?? null,
                            'file_path' => $path,
                        ]);

                        // redirect to configure page
                        return redirect(\App\Filament\Resources\ImportJobResource::getUrl('configure', ['record' => $import->id]));
                    }),
                \Filament\Tables\Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn () => route('products.export')),
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
            ImagesRelationManager::class,
            VariantsRelationManager::class,
            ActivityLogsRelationManager::class,
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['category', 'images']);
    }
}
