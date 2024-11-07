<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UsersTableSeeder::class,

            WarungSeeder::class,
            TransactionSeeder::class,
            BalanceSeeder::class,
            TransactionHistorySeeder::class,
        ]);

        Artisan::call('shield:generate --all');
    }
}
