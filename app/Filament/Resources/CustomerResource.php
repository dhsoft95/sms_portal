<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\categories;
use App\Models\Customer;
use App\Models\districts;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('fname')
                    ->required()
                    ->maxLength(255)->label('First name'),
                Forms\Components\TextInput::make('lname')
                    ->required()->label('Last name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Select::make('region_name')
                    ->label('Region')->live()->preload()
                    ->options(Region::all()->pluck('name', 'name')) // Assuming your model is named 'District'
                    ->searchable()
                    ->afterStateUpdated(fn(Set $set)=>$set('district_name',null)),
                Select::make('district_name')->searchable()->label('District')
                    ->options(fn ($get) => districts::where('region_name', $get('region_name'))
                        ->pluck('name', 'name'))->live()->preload(),
//                         ->afterStateUpdated(fn(Set $set)=>$set('region_name',null)),
                Select::make('category_name')
                    ->label('Category')->required()
                    ->options(categories::all()->pluck('name', 'name'))
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fname')
                    ->searchable()->label('First name'),
                Tables\Columns\TextColumn::make('lname')
                    ->searchable()->label('Last name'),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('district_name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('region_name')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category_name')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ManageCustomers::route('/'),
        ];
    }
}
