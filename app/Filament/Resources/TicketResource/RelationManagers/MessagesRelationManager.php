<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use App\Notifications\TicketReplied;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Conversation';

    protected static ?string $icon = 'heroicon-o-chat-bubble-left-right';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull()
                    ->placeholder('Type your reply here...'),

                Forms\Components\FileUpload::make('attachments')
                    ->label('Attachments')
                    ->multiple()
                    ->maxFiles(5)
                    ->maxSize(10240)
                    ->acceptedFileTypes(['image/*', 'application/pdf', '.doc', '.docx', '.txt'])
                    ->directory('ticket-attachments')
                    ->columnSpanFull()
                    ->helperText('Max 5 files, 10MB each. Accepted: images, PDF, Word documents, text files.'),

                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),

                Forms\Components\Hidden::make('is_admin_reply')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('From')
                    ->badge()
                    ->color(fn ($record) => $record->is_admin_reply ? 'success' : 'info')
                    ->formatStateUsing(fn ($record) => $record->is_admin_reply ? '👨‍💼 '.$record->user->name : '👤 '.$record->user->name),

                Tables\Columns\TextColumn::make('message')
                    ->limit(100)
                    ->wrap()
                    ->html()
                    ->formatStateUsing(fn (string $state): string => nl2br(e($state))),

                Tables\Columns\IconColumn::make('attachments_count')
                    ->counts('attachments')
                    ->label('📎')
                    ->alignCenter()
                    ->visible(fn ($record) => $record && $record->attachments_count > 0),

                Tables\Columns\IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Reply')
                    ->icon('heroicon-o-paper-airplane')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        $data['is_admin_reply'] = true;

                        return $data;
                    })
                    ->after(function ($record, $data) {

                        $record->ticket->update([
                            'last_reply_at' => now(),
                        ]);

                        if (isset($data['attachments']) && is_array($data['attachments'])) {
                            foreach ($data['attachments'] as $filePath) {
                                $fileName = basename($filePath);
                                $fileSize = \Storage::disk('public')->size($filePath);
                                $mimeType = \Storage::disk('public')->mimeType($filePath);

                                $record->attachments()->create([
                                    'file_name' => $fileName,
                                    'file_path' => $filePath,
                                    'file_type' => $mimeType,
                                    'file_size' => $fileSize,
                                ]);
                            }
                        }

                        $record->ticket->user->notify(new TicketReplied($record->ticket, $record));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_read')
                    ->label('Mark as Read')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->is_read && ! $record->is_admin_reply)
                    ->action(fn ($record) => $record->update(['is_read' => true])),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
