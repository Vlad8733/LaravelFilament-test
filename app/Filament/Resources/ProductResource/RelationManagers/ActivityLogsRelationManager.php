<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Forms\Form;

class ActivityLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'activityLogs';

    protected static ?string $title = 'Activity Logs';

    

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('action')
                ->disabled()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->label('User'),
            Tables\Columns\TextColumn::make('action')->wrap()->limit(120),
            Tables\Columns\TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('created_at')->label('When')->dateTime()->sortable(),
        ])->filters([])
        ->headerActions([])
        ->actions([])
        ->bulkActions([])
        ->defaultSort('created_at', 'desc');
    }
}
