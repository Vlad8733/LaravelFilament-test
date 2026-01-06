<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefundRequestResource\Pages;
use App\Models\RefundRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RefundRequestResource extends Resource
{
    protected static ?string $model = RefundRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 3;

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
                Forms\Components\Section::make('Refund Details')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->relationship('order', 'order_number')
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'completed' => 'Completed',
                            ])
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->options([
                                'full' => 'Full Refund',
                                'partial' => 'Partial Refund',
                            ])
                            ->required()
                            ->disabled(),

                        Forms\Components\TextInput::make('amount')
                            ->prefix('$')
                            ->numeric()
                            ->required()
                            ->disabled(),

                        Forms\Components\Textarea::make('reason')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes / Response')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
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

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'full',
                        'warning' => 'partial',
                    ]),

                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'approved',
                        'danger' => 'rejected',
                        'success' => 'completed',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('processed_at')
                    ->label('Processed')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'full' => 'Full Refund',
                        'partial' => 'Partial Refund',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Refund Request')
                    ->modalDescription('Are you sure you want to approve this refund?')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notes (optional)')
                            ->rows(2),
                    ])
                    ->action(function (RefundRequest $record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'admin_notes' => $data['admin_notes'] ?? null,
                            'processed_by' => auth()->id(),
                            'processed_at' => now(),
                        ]);

                        $record->addStatusHistory('approved', $data['admin_notes'] ?? __('refunds.note_approved_by_admin'), auth()->id());

                        Notification::make()
                            ->title('Refund Approved')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (RefundRequest $record) => $record->isPending()),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Refund Request')
                    ->modalDescription('Please provide a reason for rejection.')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Reason for rejection')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (RefundRequest $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'admin_notes' => $data['admin_notes'],
                            'processed_by' => auth()->id(),
                            'processed_at' => now(),
                        ]);

                        $record->addStatusHistory('rejected', $data['admin_notes'], auth()->id());

                        Notification::make()
                            ->title('Refund Rejected')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (RefundRequest $record) => $record->isPending()),

                Tables\Actions\Action::make('complete')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Complete Refund')
                    ->modalDescription('Confirm that the refund has been processed.')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notes (optional)')
                            ->rows(2),
                    ])
                    ->action(function (RefundRequest $record, array $data) {
                        $notes = $data['admin_notes'] ?? 'Refund completed.';
                        if ($record->admin_notes) {
                            $notes = $record->admin_notes."\n\n".$notes;
                        }

                        $record->update([
                            'status' => 'completed',
                            'admin_notes' => $notes,
                            'processed_by' => auth()->id(),
                            'processed_at' => now(),
                        ]);

                        $record->addStatusHistory('completed', __('refunds.note_completed'), auth()->id());

                        Notification::make()
                            ->title('Refund Completed')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (RefundRequest $record) => $record->isApproved()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Request Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('order.order_number')
                                    ->label('Order Number')
                                    ->url(fn (RefundRequest $record) => route('filament.admin.resources.orders.view', $record->order_id)),

                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Customer'),

                                Infolists\Components\TextEntry::make('user.email')
                                    ->label('Email'),
                            ]),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('type')
                                    ->badge()
                                    ->color(fn (string $state) => match ($state) {
                                        'full' => 'primary',
                                        'partial' => 'warning',
                                    }),

                                Infolists\Components\TextEntry::make('amount')
                                    ->money('USD'),

                                Infolists\Components\TextEntry::make('order.total')
                                    ->label('Order Total')
                                    ->money('USD'),

                                Infolists\Components\TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state) => match ($state) {
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'rejected' => 'danger',
                                        'completed' => 'success',
                                    }),
                            ]),
                    ]),

                Infolists\Components\Section::make('Reason')
                    ->schema([
                        Infolists\Components\TextEntry::make('reason')
                            ->hiddenLabel()
                            ->prose(),
                    ]),

                Infolists\Components\Section::make('Admin Response')
                    ->schema([
                        Infolists\Components\TextEntry::make('admin_notes')
                            ->hiddenLabel()
                            ->prose()
                            ->placeholder('No admin notes yet.'),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('processedBy.name')
                                    ->label('Processed By')
                                    ->placeholder('—'),

                                Infolists\Components\TextEntry::make('processed_at')
                                    ->label('Processed At')
                                    ->dateTime('M d, Y H:i')
                                    ->placeholder('—'),
                            ]),
                    ])
                    ->visible(fn (RefundRequest $record) => $record->admin_notes || $record->processed_at),

                Infolists\Components\Section::make('Status History')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('statusHistory')
                            ->hiddenLabel()
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state) => match ($state) {
                                                'pending' => 'warning',
                                                'approved' => 'info',
                                                'rejected' => 'danger',
                                                'completed' => 'success',
                                                default => 'gray',
                                            }),

                                        Infolists\Components\TextEntry::make('notes')
                                            ->placeholder('—'),

                                        Infolists\Components\TextEntry::make('changedByUser.name')
                                            ->label('By')
                                            ->placeholder('System'),

                                        Infolists\Components\TextEntry::make('changed_at')
                                            ->dateTime('M d, Y H:i'),
                                    ]),
                            ]),
                    ]),
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
            'index' => Pages\ListRefundRequests::route('/'),
            'view' => Pages\ViewRefundRequest::route('/{record}'),
            'edit' => Pages\EditRefundRequest::route('/{record}/edit'),
        ];
    }
}
