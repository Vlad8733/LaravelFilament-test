<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhookResource\Pages;
use App\Models\Webhook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WebhookResource extends Resource
{
    protected static ?string $model = Webhook::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 100;

    public static function getNavigationBadge(): ?string
    {
        $active = static::getModel()::where('is_active', true)->count();

        return $active > 0 ? (string) $active : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'gray';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Webhook Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com/webhook'),

                        Forms\Components\TextInput::make('secret')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText('Used to sign webhook payloads with HMAC-SHA256')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate')
                                    ->icon('heroicon-o-key')
                                    ->action(function (Forms\Set $set) {
                                        $set('secret', Str::random(32));
                                    })
                            ),

                        Forms\Components\CheckboxList::make('events')
                            ->options(Webhook::AVAILABLE_EVENTS)
                            ->required()
                            ->columns(2),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('max_retries')
                                    ->numeric()
                                    ->default(3)
                                    ->minValue(0)
                                    ->maxValue(10),

                                Forms\Components\TextInput::make('timeout_seconds')
                                    ->numeric()
                                    ->default(30)
                                    ->minValue(5)
                                    ->maxValue(120),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('url')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('events')
                    ->badge()
                    ->formatStateUsing(fn ($state) => count($state ?? []).' events'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('logs_count')
                    ->counts('logs')
                    ->label('Deliveries'),

                Tables\Columns\TextColumn::make('last_triggered_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('test')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function (Webhook $record) {
                        $service = app(\App\Services\WebhookService::class);
                        $service->send($record, 'webhook.test', [
                            'message' => 'This is a test webhook delivery',
                            'timestamp' => now()->toIso8601String(),
                        ]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send Test Webhook')
                    ->modalDescription('This will send a test payload to the webhook URL.'),
                Tables\Actions\Action::make('logs')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Webhook $record) => WebhookResource::getUrl('logs', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhooks::route('/'),
            'create' => Pages\CreateWebhook::route('/create'),
            'edit' => Pages\EditWebhook::route('/{record}/edit'),
            'logs' => Pages\WebhookLogs::route('/{record}/logs'),
        ];
    }
}
