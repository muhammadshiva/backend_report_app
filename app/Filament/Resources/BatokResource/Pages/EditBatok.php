<?php

namespace App\Filament\Resources\BatokResource\Pages;

use App\Filament\Resources\BatokResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBatok extends EditRecord
{
    protected static string $resource = BatokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
