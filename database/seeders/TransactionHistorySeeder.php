<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Warung;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use App\Models\TransactionHistory;

class TransactionHistorySeeder extends Seeder
{
    public function run()
    {
        // Mengambil transaksi dan informasi terkait
        $transactions = Transaction::all();
        $pembeli = Customer::first();
        $warung = Warung::first();

        foreach ($transactions as $transaction) {
            TransactionHistory::create([
                'transaction_id' => $transaction->id,
                'customer_id' => $pembeli->id,
                'warung_id' => $warung->id,
                'transaction_date' => Carbon::now(),
                'amount' => $transaction->amount,
            ]);
        }
    }
}
