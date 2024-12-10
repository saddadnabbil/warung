<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\CustomerResource;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user_id'] = $data['user_id'];
        $user = User::find($data['user_id']);

        $data['username'] = $user->username;
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['phone'] = $user->phone;

        return $data;
    }
}
