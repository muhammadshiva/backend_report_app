<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $controller = new \App\Http\Controllers\Api\UserController;
        $request = new \Illuminate\Http\Request($data);
        $controller->update($request, $this->record->id); // Memanggil update
        return $data;
    }
}
