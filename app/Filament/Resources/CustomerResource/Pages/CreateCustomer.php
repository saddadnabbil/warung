<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Str;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CustomerResource;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $sid = Str::uuid();

        $user = User::create([
            'id' => $sid,
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);

        $user->assignRole('pembeli');

        $data['user_id'] = $user->id;

        return $data;
    }
}
