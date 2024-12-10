<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warung;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warung = Warung::first();
        $faker = Faker::create();

        // Superadmin user
        $sid = Str::uuid();
        $pembeli = User::create([
            'id' => $sid,
            'username' => 'nabil',
            'name' => 'nabil',
            'email' => 'nabil@gmail.com',
            'phone' => $faker->phoneNumber,
            'email_verified_at' => now(),
            'password' => Hash::make('nabil'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pembeli->assignRole('pembeli');

        // Insert customer record
        DB::table('customers')->insert([
            'user_id' => $pembeli->id, // Use the UUID here
            'warung_id' => $warung->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
