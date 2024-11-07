<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warung;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $warung = Warung::first();
        $pembeli = User::role('pembeli')->first();

        // Simulasi Deposit
        Transaction::create([
            'warung_id' => $warung->id,
            'buyer_id' => $pembeli->id,
            'transaction_type' => 'deposit',
            'amount' => 100000,
            'description' => 'Deposit saldo pertama',
        ]);

        // Simulasi Pembelian
        Transaction::create([
            'warung_id' => $warung->id,
            'buyer_id' => $pembeli->id,
            'transaction_type' => 'purchase',
            'amount' => -20000,
            'description' => 'Pembelian item A',
        ]);
    }
}
