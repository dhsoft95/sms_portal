<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Filament\Resources\MessageResource\RelationManagers;
use App\Models\campaigns;
use App\Models\categories;
use App\Models\districts;
use App\Models\message;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Actions\Action;
use Tapp\FilamentTimezoneField\Forms\Components\TimezoneSelect;

class MessageResource extends Resource
{
    protected static ?string $model = message::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Operations';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('campaign_id')
                    ->label('Campaign')
                    ->options(campaigns::all()->pluck('name', 'id'))
                    ->searchable(),

                Select::make('district_name')
                    ->label('District')
                    ->options(districts::all()->pluck('name','name'))
                    ->searchable(),

                Select::make('category_name')
                    ->label('Category')
                    ->options(categories::all()->pluck('name', 'name'))
                    ->searchable(),

                Toggle::make('is_scheduled')
                    ->onColor('success')
                    ->offColor('danger')
                    ->required()
                    ->label('Schedule sms ?'),
                Section::make()
                    ->schema([
                        TagsInput::make('tags')
                            ->separator(',')
                    ]),

                Section::make('Scheduled')
                    ->id('scheduled-section') // Add an ID to the section for targeting in JavaScript
                    ->headerActions([
                    ])
                    ->description('The items you have selected for purchase')
                    ->icon('heroicon-m-clock')
                    ->schema([
                        DatePicker::make('scheduled_date'),
                        TimePicker::make('scheduled_time'),
                        TimezoneSelect::make('timezone')->byCountry('TZ'),
                        Select::make('frequency')
                            ->options([
                                'One_time' => 'One time',
                                'Daily' => 'Daily',
                                'Monthly' => 'Monthly',
                            ])
                    ])
                    ->collapsible()
                    ->compact()
                    ->columns(2)

            ])
            ->columns(3);
    }




    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('campaign.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('district_name')
                    ->searchable()->default('All District'),
                Tables\Columns\TextColumn::make('category_name')
                    ->searchable()->words(2)->default('All Categories '),
                Tables\Columns\IconColumn::make('status')->boolean(),
//                Tables\Columns\IconColumn::make('status')->boolean(),
                IconColumn::make('status')
                    ->options([
                        'heroicon-o-x-circle',
                        'heroicon-o-check' => fn ($state, $record): bool => $record->status === null,
                        'heroicon-o-arrow-path' => fn ($state): bool => $state === 0,
                        'heroicon-o-check-badge' => fn ($state): bool => $state ===1,
                    ]) ->colors([
                        'secondary',
                        'danger' => null,
                        'warning' => 0,
                        'success' => 1,
                    ]),


                Tables\Columns\IconColumn::make('is_scheduled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_time'),
                Tables\Columns\TextColumn::make('timezone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('frequency')
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
            'index' => Pages\ManageMessages::route('/'),
        ];
    }
}
