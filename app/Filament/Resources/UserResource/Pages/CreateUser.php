<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $controller = new \App\Http\Controllers\Api\UserController;
        $request = new \Illuminate\Http\Request($data);
        $controller->update($request, null); // null untuk create
        return $data;
    }
}
