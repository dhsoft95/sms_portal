<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignsResource\Pages;
use App\Filament\Resources\CampaignsResource\RelationManagers;
use App\Models\campaigns;
use App\Models\Region;
use App\Models\templates;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CampaignsResource extends Resource
{
    protected static ?string $model = campaigns::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    protected static ?string $navigationGroup = 'Operations';

    public static function form(Form $form): Form
    {
        $currentYear = now()->year;
        $campaign_code = campaigns::whereYear('created_at', $currentYear)->count() + 1;
        $campaign_code = 'SMS-' . $currentYear . '-' . str_pad($campaign_code, 3, '0', STR_PAD_LEFT);
        return $form
            ->schema([

                Forms\Components\TextInput::make('campaign_code')
                    ->maxLength(255)->default($campaign_code),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
//                Forms\Components\Textarea::make('message')
//                    ->required()
//                    ->columnSpanFull(),
                Select::make('template_id')
                    ->label('Template')->required()
                    ->options(templates::all()->pluck('name', 'id'))
                    ->searchable(),
                Select::make('region_id')
                    ->label('Region')->required()
                    ->options(Region::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\Select::make('district_id')
                    ->relationship('district', 'name')
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->required()  ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-bolt'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('campaign_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->numeric()
                    ->sortable(),
              Tables\Columns\TextColumn::make('template.name')
                  ->numeric()
                  ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
            'index' => Pages\ManageCampaigns::route('/'),
        ];
    }
}
