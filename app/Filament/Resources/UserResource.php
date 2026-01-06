<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('email')->required()->email()->maxLength(255),
            Forms\Components\Toggle::make('is_seller')->label('Seller'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('id')->label('ID')->sortable(),
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('email')->searchable()->sortable(),
            IconColumn::make('is_seller')->boolean()->label('Seller'),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])
            ->actions([
                Action::make('toggleSeller')
                    ->label(fn (User $record): string => $record->is_seller ? 'Demote' : 'Promote to Seller')
                    ->icon(fn (User $record): string => $record->is_seller ? 'heroicon-s-user-minus' : 'heroicon-s-user-plus')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->update(['is_seller' => ! $record->is_seller]);
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('makeSellers')
                    ->label('Make Sellers')
                    ->action(function ($records) {
                        $records->each->update(['is_seller' => true]);
                    }),
                Tables\Actions\BulkAction::make('removeSellers')
                    ->label('Remove Sellers')
                    ->action(function ($records) {
                        $records->each->update(['is_seller' => false]);
                    }),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
