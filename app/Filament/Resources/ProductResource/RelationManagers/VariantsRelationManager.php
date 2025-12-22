<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Variants';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('sku')
                ->maxLength(255)
                ->label('SKU'),

            Forms\Components\TextInput::make('price')
                ->numeric()
                ->label('Price')
                ->prefix('$'),

            Forms\Components\TextInput::make('sale_price')
                ->numeric()
                ->label('Sale Price')
                ->prefix('$'),

            Forms\Components\TextInput::make('stock_quantity')
                ->numeric()
                ->default(0)
                ->label('Stock'),

            Forms\Components\KeyValue::make('attributes')
                ->keyLabel('Name')
                ->valueLabel('Value')
                ->label('Attributes')
                ->columns(2),

            Forms\Components\Toggle::make('is_default')
                ->label('Default Variant'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')->label('SKU')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('price')->label('Price')->money('usd'),
                Tables\Columns\TextColumn::make('sale_price')->label('Sale Price')->money('usd')->toggleable(),
                Tables\Columns\TextColumn::make('stock_quantity')->label('Stock')->sortable(),
                Tables\Columns\IconColumn::make('is_default')->boolean()->label('Default'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add Variant'),
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
