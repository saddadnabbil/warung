<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warung;
use App\Models\User;

class WarungSeeder extends Seeder
{
    public function run()
    {
        $pemilik = User::role('pemilik_warung')->first();

        Warung::create([
            'user_id' => $pemilik->id,
            'name' => 'Warung Ibu Kurnia',
            'location' => 'Jl. Warung No.123',
        ]);
    }
}
