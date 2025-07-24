<?php

namespace App\Filament\Resources\CuttingTestResource\Pages;

use App\Filament\Resources\CuttingTestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCuttingTest extends EditRecord
{
    protected static string $resource = CuttingTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
