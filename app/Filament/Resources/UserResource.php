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
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    public static function form(Forms\Form $form): Forms\Form
    {
        $isSuperAdmin = Auth::user()?->isSuperAdmin() ?? false;
        
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('email')->required()->email()->maxLength(255),
            
            Forms\Components\Section::make('Roles & Permissions')
                ->schema([
                    Forms\Components\Select::make('role')
                        ->label('Role')
                        ->options([
                            User::ROLE_USER => 'User',
                            User::ROLE_SELLER => 'Seller', 
                            User::ROLE_ADMIN => 'Admin',
                        ])
                        ->default(User::ROLE_USER)
                        ->disabled(fn () => !$isSuperAdmin)
                        ->helperText(fn () => !$isSuperAdmin ? 'Only Super Admin can change roles' : null),
                        
                    Forms\Components\Toggle::make('is_seller')
                        ->label('Is Seller (legacy)')
                        ->disabled(fn () => !$isSuperAdmin)
                        ->helperText(fn () => !$isSuperAdmin ? 'Only Super Admin can change this' : null),
                ])
                ->columns(2)
                ->visible(fn () => $isSuperAdmin),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('id')->label('ID')->sortable(),
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('email')->searchable()->sortable(),
            TextColumn::make('role')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'super_admin' => 'danger',
                    'admin' => 'warning',
                    'seller' => 'success',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
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
                    })
                    ->visible(fn () => Auth::user()?->isSuperAdmin()),
                    
                Action::make('makeAdmin')
                    ->label(fn (User $record): string => $record->role === User::ROLE_ADMIN ? 'Remove Admin' : 'Make Admin')
                    ->icon(fn (User $record): string => $record->role === User::ROLE_ADMIN ? 'heroicon-s-shield-exclamation' : 'heroicon-s-shield-check')
                    ->color(fn (User $record): string => $record->role === User::ROLE_ADMIN ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $newRole = $record->role === User::ROLE_ADMIN ? User::ROLE_USER : User::ROLE_ADMIN;
                        $record->update(['role' => $newRole]);
                    })
                    ->visible(fn (User $record) => Auth::user()?->isSuperAdmin() && !$record->isSuperAdmin()),
                    
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('makeSellers')
                    ->label('Make Sellers')
                    ->action(function ($records) {
                        $records->each->update(['is_seller' => true]);
                    })
                    ->visible(fn () => Auth::user()?->isSuperAdmin()),
                Tables\Actions\BulkAction::make('removeSellers')
                    ->label('Remove Sellers')
                    ->action(function ($records) {
                        $records->each->update(['is_seller' => false]);
                    })
                    ->visible(fn () => Auth::user()?->isSuperAdmin()),
                Tables\Actions\BulkAction::make('makeAdmins')
                    ->label('Make Admins')
                    ->action(function ($records) {
                        $records->each(function ($record) {
                            if (!$record->isSuperAdmin()) {
                                $record->update(['role' => User::ROLE_ADMIN]);
                            }
                        });
                    })
                    ->visible(fn () => Auth::user()?->isSuperAdmin()),
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
