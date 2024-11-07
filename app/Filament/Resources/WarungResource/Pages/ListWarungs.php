<?php

namespace App\Filament\Resources\WarungResource\Pages;

use App\Filament\Resources\WarungResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarungs extends ListRecords
{
    protected static string $resource = WarungResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
