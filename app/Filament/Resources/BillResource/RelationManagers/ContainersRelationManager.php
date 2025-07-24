<?php

namespace App\Filament\Resources\BillResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContainersRelationManager extends RelationManager
{
    protected static string $relationship = 'containers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('container_number')
                    ->required()
                    ->maxLength(11),
                Forms\Components\TextInput::make('truck')
                    ->maxLength(20),
                Forms\Components\TextInput::make('quantity_of_bags')
                    ->numeric(),
                Forms\Components\TextInput::make('w_net')
                    ->numeric()
                    ->step('0.01'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('container_number')
            ->columns([
                Tables\Columns\TextColumn::make('container_number')->sortable(),
                Tables\Columns\TextColumn::make('truck'),
                Tables\Columns\TextColumn::make('quantity_of_bags'),
                Tables\Columns\TextColumn::make('w_net'),
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
