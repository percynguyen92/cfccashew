<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContainerResource\Pages;
use App\Filament\Resources\ContainerResource\RelationManagers\CuttingTestRelationManager;
use App\Models\Container;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContainerResource extends Resource
{
    protected static ?string $model = Container::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('bill_id')
                    ->relationship('bill', 'billNumber')
                    ->required(),
                Forms\Components\TextInput::make('truck')
                    ->maxLength(20),
                Forms\Components\TextInput::make('container_number')
                    ->maxLength(11),
                Forms\Components\TextInput::make('quantity_of_bags')
                    ->numeric(),
                Forms\Components\TextInput::make('w_net')
                    ->numeric()
                    ->step('0.01'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bill.billNumber')->label('Bill')->sortable(),
                Tables\Columns\TextColumn::make('container_number')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('truck'),
                Tables\Columns\TextColumn::make('quantity_of_bags'),
                Tables\Columns\TextColumn::make('w_net'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            CuttingTestRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContainers::route('/'),
            'create' => Pages\CreateContainer::route('/create'),
            'edit' => Pages\EditContainer::route('/{record}/edit'),
        ];
    }
}
