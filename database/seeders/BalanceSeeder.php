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
        // Mengambil user pembeli
        $pembeli = Customer::first();

        // Menghitung balance awal sesuai transaksi (misalnya, deposit 100000 - purchase 20000)
        $initialBalance = 100000 - 20000;

        // Membuat record balance
        Balance::create([
            'customer_id' => $pembeli->id,
            'balance' => $initialBalance,
        ]);
    }
}
