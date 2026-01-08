<?php

namespace App\Filament\Seller\Resources;

use App\Filament\Seller\Resources\ProductChatResource\Pages;
use App\Models\ProductChat;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductChatResource extends Resource
{
    protected static ?string $model = ProductChat::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Product Chats';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('seller_id', Auth::id())
            ->whereHas('messages', function ($query) {
                $query->where('is_seller', false)
                    ->where('is_read', false);
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('seller_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form not needed for chat
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product.primary_image_url')
                    ->label('Product')
                    ->circular()
                    ->defaultImageUrl(asset('images/placeholder.png')),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('latestMessage.message')
                    ->label('Last Message')
                    ->limit(50)
                    ->default('No messages yet'),

                Tables\Columns\BadgeColumn::make('unread_count')
                    ->label('Unread')
                    ->getStateUsing(function (ProductChat $record) {
                        return $record->messages()
                            ->where('is_seller', false)
                            ->where('is_read', false)
                            ->count();
                    })
                    ->colors([
                        'success' => 0,
                        'warning' => fn ($state) => $state > 0,
                    ])
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state : '✓'),

                Tables\Columns\TextColumn::make('last_message_at')
                    ->label('Last Activity')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'open',
                        'gray' => 'closed',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Open Chat'),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductChats::route('/'),
            'view' => Pages\ViewProductChat::route('/{record}'),
        ];
    }
}
