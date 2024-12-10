<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Balance;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class BalanceSeeder extends Seeder
{
    public function run()
    {
        $pembeli = Customer::first();

        Balance::updateOrCreate(
            ['customer_id' => $pembeli->id],
            ['balance' => 0]
        );
    }
}
