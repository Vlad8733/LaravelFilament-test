<?php

namespace App\Filament\Seller\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Product Variants';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('sku')
                ->maxLength(255)
                ->label('SKU')
                ->helperText('Unique identifier for this variant'),

            Forms\Components\TextInput::make('price')
                ->numeric()
                ->required()
                ->label('Price')
                ->prefix('$')
                ->step(0.01),

            Forms\Components\TextInput::make('sale_price')
                ->numeric()
                ->label('Sale Price')
                ->prefix('$')
                ->step(0.01),

            Forms\Components\TextInput::make('stock_quantity')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->label('Stock Quantity'),

            Forms\Components\KeyValue::make('attributes')
                ->keyLabel('Attribute')
                ->valueLabel('Value')
                ->label('Variant Attributes')
                ->helperText('E.g., Color: Red, Size: XL')
                ->reorderable()
                ->addActionLabel('Add Attribute'),

            Forms\Components\Toggle::make('is_default')
                ->label('Default Variant')
                ->helperText('This variant will be selected by default'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('attributes')
                    ->label('Attributes')
                    ->formatStateUsing(function ($state) {
                        if (! $state || ! is_array($state)) {
                            return '-';
                        }

                        return collect($state)->map(fn ($v, $k) => "$k: $v")->join(', ');
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('usd'),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Sale')
                    ->money('usd')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state > 10 => 'success',
                        $state > 0 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Variant'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
