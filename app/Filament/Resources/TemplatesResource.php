<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplatesResource\Pages;
//use App\Filament\Resources\TemplatesResource\RelationManagers;
use App\Models\templates;
use Filament\Forms;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TemplatesResource extends Resource
{
    protected static ?string $model = templates::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'Operations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('Tags')
                    ->label('Available Tag')
                    ->searchable()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('content', '{'.$state.'}');
                    })
                    ->reactive()
                    ->preload()
                    ->options([
                        'promotional' => 'Promotional',
                        'transactional' => 'Transactional',
                        'reminder' => 'Reminder',
                        'event' => 'Event',
                        'survey' => 'Survey',
                        'update' => 'Update',
                        'welcome' => 'Welcome',
                        'alert' => 'Alert',
                        'feedback' => 'Feedback',
                        'educational' => 'Educational',
                    ]),

                Forms\Components\Textarea::class::make('content')
                    ->required()
                    ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTemplates::route('/'),
        ];
    }
}
