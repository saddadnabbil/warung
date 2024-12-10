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

        // Ambil semua peran kecuali super_admin
        $roles = DB::table('roles')->where('name', '!=', 'super_admin')->get();

        foreach ($roles as $role) {
            for ($i = 0; $i < 10; $i++) {
                $userId = Str::uuid(); // Generate UUID for each user
                $user = User::create([
                    'id' => $userId,
                    'username' => $faker->unique()->userName,
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'phone' => $faker->phoneNumber,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $user->assignRole($role->name);

                // Insert customer record
                DB::table('customers')->insert([
                    'user_id' => $userId, // Use the UUID here
                    'warung_id' => $warung->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
