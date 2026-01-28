<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Ban;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $navigationGroup = 'Users & Sellers';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

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
                        ->disabled(fn () => ! $isSuperAdmin)
                        ->helperText(fn () => ! $isSuperAdmin ? 'Only Super Admin can change roles' : null),

                    Forms\Components\Toggle::make('is_seller')
                        ->label('Is Seller (legacy)')
                        ->disabled(fn () => ! $isSuperAdmin)
                        ->helperText(fn () => ! $isSuperAdmin ? 'Only Super Admin can change this' : null),
                ])
                ->columns(2)
                ->visible(fn () => $isSuperAdmin),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('id')->label('ID')->sortable(),
            TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->formatStateUsing(fn (string $state, User $record): string => $record->id === Auth::id() ? $state.' (You)' : $state)
                ->color(fn (User $record): ?string => $record->id === Auth::id() ? 'primary' : null)
                ->weight(fn (User $record): ?string => $record->id === Auth::id() ? 'bold' : null),
            TextColumn::make('email')
                ->searchable()
                ->sortable()
                ->formatStateUsing(function (string $state, User $record): string {

                    if ($record->id === Auth::id()) {
                        return $state;
                    }

                    $parts = explode('@', $state);
                    if (count($parts) === 2) {
                        $local = $parts[0];
                        $domain = $parts[1];
                        $masked = substr($local, 0, 2).str_repeat('•', max(strlen($local) - 2, 3));

                        return $masked.'@'.$domain;
                    }

                    return '••••@••••';
                })
                ->copyable()
                ->copyableState(fn (User $record): string => $record->id === Auth::id() ? $record->email : 'Hidden'),
            TextColumn::make('role')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'super_admin' => 'danger',
                    'admin' => 'warning',
                    'seller' => 'success',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

            Tables\Columns\IconColumn::make('is_banned')
                ->label('Banned')
                ->boolean()
                ->getStateUsing(fn (User $record) => Ban::checkAccountBan($record->id) !== null)
                ->trueIcon('heroicon-o-no-symbol')
                ->falseIcon('heroicon-o-check-circle')
                ->trueColor('danger')
                ->falseColor('success'),

            TextColumn::make('created_at')->dateTime()->sortable(),
        ])
            ->actions([

                Action::make('banAccount')
                    ->label('Ban')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->modalHeading('Ban User Account')
                    ->form([
                        Forms\Components\Select::make('reason')
                            ->label('Reason')
                            ->options(Ban::REASONS)
                            ->required(),
                        Forms\Components\Select::make('duration')
                            ->label('Duration')
                            ->options(Ban::DURATIONS)
                            ->default('permanent')
                            ->required()
                            ->live(),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->visible(fn (Forms\Get $get) => $get('duration') === 'custom')
                            ->required(fn (Forms\Get $get) => $get('duration') === 'custom')
                            ->minDate(now()),
                        Forms\Components\Textarea::make('public_message')
                            ->label('Message to User')
                            ->rows(2),
                        Forms\Components\Textarea::make('admin_comment')
                            ->label('Admin Notes (Private)')
                            ->rows(2),
                    ])
                    ->action(function (User $record, array $data) {
                        $expiresAt = null;
                        if ($data['duration'] === 'custom') {
                            $expiresAt = $data['expires_at'];
                        } elseif ($data['duration'] !== 'permanent') {
                            $expiresAt = self::calculateExpiration($data['duration']);
                        }

                        Ban::banAccount(
                            $record->id,
                            $data['reason'],
                            $data['admin_comment'] ?? null,
                            $data['public_message'] ?? null,
                            $expiresAt ? new \DateTime($expiresAt) : null,
                            Auth::id()
                        );

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('User banned')
                            ->send();
                    })
                    ->visible(fn (User $record) => $record->id !== Auth::id() &&
                        ! $record->isSuperAdmin() &&
                        Ban::checkAccountBan($record->id) === null
                    ),

                Action::make('unban')
                    ->label('Unban')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Unban User')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason for unban')
                            ->required(),
                    ])
                    ->action(function (User $record, array $data) {
                        $ban = Ban::checkAccountBan($record->id);
                        if ($ban) {
                            $ban->unban(Auth::id(), $data['reason']);

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('User unbanned')
                                ->send();
                        }
                    })
                    ->visible(fn (User $record) => Ban::checkAccountBan($record->id) !== null),

                Tables\Actions\ActionGroup::make([
                    Action::make('banIp')
                        ->label('Ban IP')
                        ->icon('heroicon-o-globe-alt')
                        ->color('danger')
                        ->modalHeading('Ban User IP Address')
                        ->form([
                            Forms\Components\Select::make('reason')
                                ->label('Reason')
                                ->options(Ban::REASONS)
                                ->required(),
                            Forms\Components\Select::make('duration')
                                ->label('Duration')
                                ->options(Ban::DURATIONS)
                                ->default('7_days')
                                ->required(),
                            Forms\Components\Textarea::make('admin_comment')
                                ->label('Admin Notes')
                                ->rows(2),
                        ])
                        ->action(function (User $record, array $data) {
                            $lastLogin = $record->loginHistories()->latest()->first();
                            if (! $lastLogin) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('No IP found')
                                    ->body('User has no login history.')
                                    ->send();

                                return;
                            }

                            $expiresAt = $data['duration'] !== 'permanent'
                                ? self::calculateExpiration($data['duration'])
                                : null;

                            Ban::banIp(
                                $lastLogin->ip_address,
                                $data['reason'],
                                $record->id,
                                $data['admin_comment'] ?? null,
                                null,
                                $expiresAt ? new \DateTime($expiresAt) : null,
                                Auth::id()
                            );

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('IP banned')
                                ->body("IP: {$lastLogin->ip_address}")
                                ->send();
                        })
                        ->visible(fn (User $record) => $record->id !== Auth::id() &&
                            ! $record->isSuperAdmin() &&
                            Auth::user()?->role === 'super_admin'
                        ),

                    Action::make('viewBans')
                        ->label('View Bans')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->url(fn (User $record) => BanResource::getUrl('index', ['tableFilters[user_id][value]' => $record->id]))
                        ->visible(fn (User $record) => Ban::where('user_id', $record->id)->exists()),
                ])->icon('heroicon-o-ellipsis-vertical'),

                Action::make('toggleSeller')
                    ->label(fn (User $record): string => $record->is_seller ? 'Demote' : 'Promote to Seller')
                    ->icon(fn (User $record): string => $record->is_seller ? 'heroicon-s-user-minus' : 'heroicon-s-user-plus')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->update(['is_seller' => ! $record->is_seller]);
                    })
                    ->visible(fn (User $record) => Auth::user()?->isSuperAdmin() && $record->id !== Auth::id()),

                Action::make('makeAdmin')
                    ->label(fn (User $record): string => $record->role === User::ROLE_ADMIN ? 'Remove Admin' : 'Make Admin')
                    ->icon(fn (User $record): string => $record->role === User::ROLE_ADMIN ? 'heroicon-s-shield-exclamation' : 'heroicon-s-shield-check')
                    ->color(fn (User $record): string => $record->role === User::ROLE_ADMIN ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $newRole = $record->role === User::ROLE_ADMIN ? User::ROLE_USER : User::ROLE_ADMIN;
                        $record->update(['role' => $newRole]);
                    })
                    ->visible(fn (User $record) => Auth::user()?->isSuperAdmin() && ! $record->isSuperAdmin() && $record->id !== Auth::id()),

                Tables\Actions\EditAction::make()
                    ->visible(fn (User $record) => $record->id !== Auth::id() || ! Auth::user()?->isSuperAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('makeSellers')
                    ->label('Make Sellers')
                    ->action(function ($records) {
                        $records->filter(fn ($r) => $r->id !== Auth::id())->each->update(['is_seller' => true]);
                    })
                    ->visible(fn () => Auth::user()?->isSuperAdmin()),
                Tables\Actions\BulkAction::make('removeSellers')
                    ->label('Remove Sellers')
                    ->action(function ($records) {
                        $records->filter(fn ($r) => $r->id !== Auth::id())->each->update(['is_seller' => false]);
                    })
                    ->visible(fn () => Auth::user()?->isSuperAdmin()),
                Tables\Actions\BulkAction::make('makeAdmins')
                    ->label('Make Admins')
                    ->action(function ($records) {
                        $records->filter(fn ($r) => $r->id !== Auth::id() && ! $r->isSuperAdmin())
                            ->each(function ($record) {
                                $record->update(['role' => User::ROLE_ADMIN]);
                            });
                    })
                    ->visible(fn () => Auth::user()?->isSuperAdmin()),
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function ($records) {
                        $records->filter(fn ($r) => $r->id !== Auth::id() && ! $r->isSuperAdmin())->each->delete();
                    }),
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

    protected static function calculateExpiration(string $duration): string
    {
        return match ($duration) {
            '1_hour' => now()->addHour()->toDateTimeString(),
            '6_hours' => now()->addHours(6)->toDateTimeString(),
            '24_hours' => now()->addDay()->toDateTimeString(),
            '3_days' => now()->addDays(3)->toDateTimeString(),
            '7_days' => now()->addWeek()->toDateTimeString(),
            '14_days' => now()->addWeeks(2)->toDateTimeString(),
            '30_days' => now()->addMonth()->toDateTimeString(),
            '90_days' => now()->addMonths(3)->toDateTimeString(),
            '180_days' => now()->addMonths(6)->toDateTimeString(),
            '365_days' => now()->addYear()->toDateTimeString(),
            default => now()->addDay()->toDateTimeString(),
        };
    }
}
