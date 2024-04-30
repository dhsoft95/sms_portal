<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignsResource\Pages;
use App\Filament\Resources\CampaignsResource\RelationManagers;
use App\Models\Campaigns;
use App\Models\categories;
use App\Models\districts;
use App\Models\Region;
use App\Models\templates;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use NunoMaduro\Collision\Adapters\Phpunit\State;
use Tapp\FilamentTimezoneField\Forms\Components\TimezoneSelect;

class CampaignsResource extends Resource
{
    protected static ?string $model = Campaigns::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $currentYear = now()->year;
        $campaign_code = campaigns::whereYear('created_at', $currentYear)->count() + 1;
        $campaign_code = 'SMS-' . $currentYear . '-' . str_pad($campaign_code, 3, '0', STR_PAD_LEFT);
        return $form
            ->schema([
                Section::make('Heading')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('template_id')
                            ->label('Template')->required()
                            ->options(templates::all()->pluck('name', 'id'))
                            ->searchable(),
                        Select::make('region_name')
                            ->label('Region')->live()->preload()
                            ->options(Region::all()->pluck('name', 'name')) // Assuming your model is named 'District'
                            ->searchable()
                            ->afterStateUpdated(fn(Set $set)=>$set('district_name',null)),
                        Select::make('category_name')
                            ->label('Category')
                            ->options(categories::all()->pluck('name', 'name'))
                            ->searchable(),
                        Select::make('district_name')->searchable()->label('District')
                            ->options(fn ($get) => districts::where('region_name', $get('region_name'))
                                ->pluck('name', 'name'))->live()->preload(),

                        Forms\Components\Toggle::make('is_scheduled'),
                    ])
                    ->columns(3),




                         Section::make('Scheduled')
                             ->id('scheduled-section') // Add an ID to the section for targeting in JavaScript
                             ->headerActions([
                             ])
                             ->description('Schedule messages for your upcoming campaign')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('template.name')
                    ->numeric()
                    ->sortable(),
//                Tables\Columns\TextColumn::make('region_name')
//                    ->numeric()
//                    ->sortable(),
//                Tables\Columns\TextColumn::make('category_name')
//                    ->searchable()->words(2)->default('All Categories '),
//                Tables\Columns\TextColumn::make('district_name')
//                    ->numeric()
//                    ->sortable(),
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
//                Tables\Columns\TextColumn::make('scheduled_date')
//                    ->date()
//                    ->sortable(),
//                Tables\Columns\TextColumn::make('scheduled_time'),
//                Tables\Columns\TextColumn::make('timezone')
//                    ->searchable(),
//                Tables\Columns\TextColumn::make('frequency')
//                    ->searchable(),
//                Tables\Columns\TextColumn::make('created_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
//                Tables\Columns\TextColumn::make('updated_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ManageCampaigns::route('/'),
        ];
    }
}
