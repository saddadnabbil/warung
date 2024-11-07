<?php

namespace App\Filament\Resources\WarungResource\Pages;

use App\Filament\Resources\WarungResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarung extends EditRecord
{
    protected static string $resource = WarungResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
