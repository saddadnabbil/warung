<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Tambahkan peran super_admin jika belum ada
        Role::firstOrCreate(['name' => 'super_admin']);

        // Superadmin user
        $sid = Str::uuid();
        DB::table('users')->insert([
            'id' => $sid,
            'username' => 'superadmin',
            'name' => 'Super Admin',
            'email' => 'superadmin@starter-kit.com',
            'phone' => $faker->phoneNumber,
            'email_verified_at' => now(),
            'password' => Hash::make('superadmin'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Bind superadmin user to FilamentShield
        Artisan::call('shield:super-admin', ['--user' => $sid]);


        // Tambahkan peran super_admin jika belum ada
        Role::firstOrCreate(['name' => 'pemilik_warung']);

        // Superadmin user
        $sid = Str::uuid();
        DB::table('users')->insert([
            'id' => $sid,
            'username' => 'kurnia',
            'name' => 'Kurnia',
            'email' => 'kurnia@gmail.com',
            'phone' => $faker->phoneNumber,
            'email_verified_at' => now(),
            'password' => Hash::make('kurnia'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pemilik = DB::table('model_has_roles')->insert([
            'role_id' => Role::where('name', 'pemilik_warung')->first()->id,
            'model_type' => 'App\Models\User',
            'model_id' => $sid,
        ]);

        // Tambahkan peran super_admin jika belum ada
        Role::firstOrCreate(['name' => 'pembeli']);

        // Superadmin user
        $sid = Str::uuid();
        $pembeli = DB::table('users')->insert([
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

        $pembeli = DB::table('model_has_roles')->insert([
            'role_id' => Role::where('name', 'pembeli')->first()->id,
            'model_type' => 'App\Models\User',
            'model_id' => $sid,
        ]);

        // Ambil semua peran kecuali super_admin
        $roles = DB::table('roles')->where('name', '!=', 'super_admin')->get();

        foreach ($roles as $role) {
            for ($i = 0; $i < 10; $i++) {
                $userId = Str::uuid();
                DB::table('users')->insert([
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
                DB::table('model_has_roles')->insert([
                    'role_id' => $role->id,
                    'model_type' => 'App\Models\User',
                    'model_id' => $userId,
                ]);
            }
        }
    }
}
