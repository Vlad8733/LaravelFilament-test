<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Companies';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['owner'])
            ->withCount(['products', 'followers']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Company Name'),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->label('URL Slug'),

                        Forms\Components\Select::make('user_id')
                            ->relationship('owner', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Owner')
                            ->disabled(),

                        Textarea::make('short_description')
                            ->maxLength(500)
                            ->label('Short Description')
                            ->rows(2),

                        Textarea::make('description')
                            ->maxLength(5000)
                            ->label('Full Description')
                            ->rows(5),
                    ])
                    ->columns(2),

                Section::make('Branding')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Company Logo')
                            ->image()
                            ->directory('companies/logos')
                            ->visibility('public'),

                        FileUpload::make('banner')
                            ->label('Banner Image')
                            ->image()
                            ->directory('companies/banners')
                            ->visibility('public'),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->label('Contact Email'),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(50)
                            ->label('Phone Number'),

                        TextInput::make('website')
                            ->url()
                            ->maxLength(255)
                            ->label('Website'),

                        TextInput::make('address')
                            ->maxLength(255),

                        TextInput::make('city')
                            ->maxLength(100),

                        TextInput::make('country')
                            ->maxLength(100),
                    ])
                    ->columns(3),

                Section::make('Status & Verification')
                    ->schema([
                        Toggle::make('is_verified')
                            ->label('Verified Company')
                            ->helperText('Verified companies get a badge on their profile'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive companies are hidden from public'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->size(50),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->sortable(),

                Tables\Columns\TextColumn::make('followers_count')
                    ->counts('followers')
                    ->label('Followers')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->label('Verified')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Verified'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Company $record) => ! $record->is_verified)
                    ->action(fn (Company $record) => $record->update(['is_verified' => true]))
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('unverify')
                    ->label('Unverify')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (Company $record) => $record->is_verified)
                    ->action(fn (Company $record) => $record->update(['is_verified' => false]))
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('view_public')
                    ->label('View Page')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Company $record) => route('companies.show', $record->slug))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('verify_selected')
                    ->label('Verify Selected')
                    ->icon('heroicon-o-check-badge')
                    ->action(fn ($records) => $records->each->update(['is_verified' => true]))
                    ->requiresConfirmation(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
