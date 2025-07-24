<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuttingTestResource\Pages;
use App\Models\CuttingTest;
use App\Enums\CuttingTestType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CuttingTestResource extends Resource
{
    protected static ?string $model = CuttingTest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('bill_id')
                    ->relationship('bill', 'billNumber')
                    ->required(),
                Forms\Components\Select::make('container_id')
                    ->relationship('container', 'container_number')
                    ->searchable(),
                Forms\Components\Select::make('type')
                    ->options([
                        1 => 'Final Sample First Cut',
                        2 => 'Final Sample Second Cut',
                        3 => 'Final Sample Third Cut',
                        4 => 'Container Cut',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('moisture')
                    ->numeric()
                    ->step('0.01'),
                Forms\Components\TextInput::make('outturn_rate')
                    ->numeric()
                    ->step('0.01'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bill.billNumber')->label('Bill')->sortable(),
                Tables\Columns\TextColumn::make('container.container_number')->label('Container'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('moisture'),
                Tables\Columns\TextColumn::make('outturn_rate'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuttingTests::route('/'),
            'create' => Pages\CreateCuttingTest::route('/create'),
            'edit' => Pages\EditCuttingTest::route('/{record}/edit'),
        ];
    }
}
