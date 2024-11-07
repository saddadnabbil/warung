<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Balance;
use App\Models\User;

class BalanceSeeder extends Seeder
{
    public function run()
    {
        // Mengambil user pembeli
        $pembeli = User::role('pembeli')->first();

        // Menghitung balance awal sesuai transaksi (misalnya, deposit 100000 - purchase 20000)
        $initialBalance = 100000 - 20000;

        // Membuat record balance
        Balance::create([
            'buyer_id' => $pembeli->id,
            'balance' => $initialBalance,
        ]);
    }
}
