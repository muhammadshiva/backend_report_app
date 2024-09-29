<?php

namespace App\Filament\Resources\BatokResource\Pages;

use App\Filament\Resources\BatokResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBatoks extends ListRecords
{
    protected static string $resource = BatokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
