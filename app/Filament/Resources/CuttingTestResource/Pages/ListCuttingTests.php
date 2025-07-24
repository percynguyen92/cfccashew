<?php

namespace App\Filament\Resources\CuttingTestResource\Pages;

use App\Filament\Resources\CuttingTestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCuttingTests extends ListRecords
{
    protected static string $resource = CuttingTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
