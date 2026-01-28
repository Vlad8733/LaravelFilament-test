<?php

namespace App\Filament\Seller\Resources;

use App\Filament\Seller\Resources\CompanyResource\Pages;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Company';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'My Company';

    protected static ?string $modelLabel = 'Company';

    protected static ?string $pluralModelLabel = 'Company';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        if ($user && $user->company) {
            return false;
        }

        return true;
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
                            ->label('Company Name')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, ?Company $record) {
                                if (! $record) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->label('URL Slug')
                            ->rules(['alpha_dash'])
                            ->helperText('Used in the company page URL'),

                        Textarea::make('short_description')
                            ->maxLength(500)
                            ->label('Short Description')
                            ->rows(2)
                            ->helperText('Brief description shown in company cards'),

                        Textarea::make('description')
                            ->maxLength(5000)
                            ->label('Full Description')
                            ->rows(5)
                            ->helperText('Detailed description shown on company page'),
                    ])
                    ->columns(2),

                Section::make('Branding')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Company Logo')
                            ->image()
                            ->directory('companies/logos')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->helperText('Square image recommended (min 200x200px)'),

                        FileUpload::make('banner')
                            ->label('Banner Image')
                            ->image()
                            ->directory('companies/banners')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['16:9', '3:1'])
                            ->helperText('Wide image for company page header'),
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
                    ])
                    ->columns(3),

                Section::make('Address')
                    ->schema([
                        TextInput::make('address')
                            ->maxLength(255)
                            ->label('Street Address'),

                        TextInput::make('city')
                            ->maxLength(100)
                            ->label('City'),

                        TextInput::make('country')
                            ->maxLength(100)
                            ->label('Country'),
                    ])
                    ->columns(3),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive companies are hidden from public'),
                    ]),
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

                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products'),

                Tables\Columns\TextColumn::make('followers_count')
                    ->counts('followers')
                    ->label('Followers'),

                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->label('Verified'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_public')
                    ->label('View Page')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Company $record) => route('companies.show', $record->slug))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
