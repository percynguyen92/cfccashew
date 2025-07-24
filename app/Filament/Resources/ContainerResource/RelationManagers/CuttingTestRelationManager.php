<?php

namespace App\Filament\Resources\ContainerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CuttingTestRelationManager extends RelationManager
{
    protected static string $relationship = 'cuttingTest';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        1 => 'Final Sample First Cut',
                        2 => 'Final Sample Second Cut',
                        3 => 'Final Sample Third Cut',
                        4 => 'Container Cut',
                    ])->required(),
                Forms\Components\TextInput::make('moisture')
                    ->numeric()
                    ->step('0.01'),
                Forms\Components\TextInput::make('outturn_rate')
                    ->numeric()
                    ->step('0.01'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('moisture'),
                Tables\Columns\TextColumn::make('outturn_rate'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
