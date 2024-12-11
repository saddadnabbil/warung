<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warung;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $warung = Warung::first();
        $pembeli =  Customer::where('warung_id', $warung->id)->first();

        // Simulasi Deposit
        Transaction::create([
            'warung_id' => $warung->id,
            'customer_id' => $pembeli->id,
            'transaction_type' => 'deposit',
            'amount' => 100000,
            'paid' => true,
            'description' => 'Deposit saldo pertama',
        ]);

        // Simulasi Pembelian
        Transaction::create([
            'warung_id' => $warung->id,
            'customer_id' => $pembeli->id,
            'transaction_type' => 'purchase',
            'amount' => 20000,
            'paid' => true,
            'description' => 'Pembelian item A',
        ]);
    }
}
