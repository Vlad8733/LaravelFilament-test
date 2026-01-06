<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerReviewResource\Pages;
use App\Models\CustomerReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerReviewResource extends Resource
{
    protected static ?string $model = CustomerReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Reviews';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Review Information')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->relationship('order', 'order_number')
                            ->disabled(),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Ratings')
                    ->schema([
                        Forms\Components\TextInput::make('delivery_rating')
                            ->label('Delivery Rating')
                            ->disabled()
                            ->suffix('/ 5'),

                        Forms\Components\TextInput::make('packaging_rating')
                            ->label('Packaging Rating')
                            ->disabled()
                            ->suffix('/ 5'),

                        Forms\Components\TextInput::make('product_rating')
                            ->label('Product Rating')
                            ->disabled()
                            ->suffix('/ 5'),

                        Forms\Components\TextInput::make('overall_rating')
                            ->label('Overall Rating')
                            ->disabled()
                            ->suffix('/ 5'),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Comment')
                    ->schema([
                        Forms\Components\Textarea::make('comment')
                            ->disabled()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Moderation')
                    ->schema([
                        Forms\Components\Textarea::make('moderation_notes')
                            ->label('Moderation Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('overall_rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => '⭐ '.number_format($state, 1))
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\IconColumn::make('comment')
                    ->label('Has Comment')
                    ->boolean()
                    ->getStateUsing(fn ($record) => ! empty($record->comment)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('overall_rating')
                    ->label('Rating')
                    ->options([
                        '5' => '5 Stars',
                        '4' => '4+ Stars',
                        '3' => '3+ Stars',
                        '2' => '2+ Stars',
                        '1' => '1+ Stars',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value']) {
                            $query->where('overall_rating', '>=', $data['value']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (CustomerReview $record) {
                        $record->update([
                            'status' => 'approved',
                            'moderated_by' => auth()->id(),
                            'moderated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Review Approved')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (CustomerReview $record) => $record->isPending()),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Review')
                    ->form([
                        Forms\Components\Textarea::make('moderation_notes')
                            ->label('Reason for rejection')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (CustomerReview $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'moderation_notes' => $data['moderation_notes'],
                            'moderated_by' => auth()->id(),
                            'moderated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Review Rejected')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (CustomerReview $record) => $record->isPending()),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->isPending()) {
                                    $record->update([
                                        'status' => 'approved',
                                        'moderated_by' => auth()->id(),
                                        'moderated_at' => now(),
                                    ]);
                                }
                            });

                            Notification::make()
                                ->title('Reviews Approved')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Review Details')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('order.order_number')
                                    ->label('Order'),

                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Customer'),

                                Infolists\Components\TextEntry::make('product.name')
                                    ->label('Product'),
                            ]),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('delivery_rating')
                                    ->label('Delivery')
                                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state)),

                                Infolists\Components\TextEntry::make('packaging_rating')
                                    ->label('Packaging')
                                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state)),

                                Infolists\Components\TextEntry::make('product_rating')
                                    ->label('Product')
                                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state)),

                                Infolists\Components\TextEntry::make('overall_rating')
                                    ->label('Overall')
                                    ->formatStateUsing(fn ($state) => '⭐ '.number_format($state, 1).' / 5'),
                            ]),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                            }),
                    ]),

                Infolists\Components\Section::make('Comment')
                    ->schema([
                        Infolists\Components\TextEntry::make('comment')
                            ->hiddenLabel()
                            ->prose()
                            ->placeholder('No comment provided.'),
                    ]),

                Infolists\Components\Section::make('Moderation')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('moderatedBy.name')
                                    ->label('Moderated By')
                                    ->placeholder('—'),

                                Infolists\Components\TextEntry::make('moderated_at')
                                    ->label('Moderated At')
                                    ->dateTime('M d, Y H:i')
                                    ->placeholder('—'),
                            ]),

                        Infolists\Components\TextEntry::make('moderation_notes')
                            ->label('Notes')
                            ->placeholder('No moderation notes.'),
                    ])
                    ->visible(fn (CustomerReview $record) => $record->moderated_at !== null),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerReviews::route('/'),
            'view' => Pages\ViewCustomerReview::route('/{record}'),
            'edit' => Pages\EditCustomerReview::route('/{record}/edit'),
        ];
    }
}
