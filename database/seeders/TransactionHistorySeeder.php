<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransactionHistory;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warung;
use Carbon\Carbon;

class TransactionHistorySeeder extends Seeder
{
    public function run()
    {
        // Mengambil transaksi dan informasi terkait
        $transactions = Transaction::all();
        $pembeli = User::role('pembeli')->first();
        $warung = Warung::first();

        foreach ($transactions as $transaction) {
            TransactionHistory::create([
                'transaction_id' => $transaction->id,
                'buyer_id' => $pembeli->id,
                'warung_id' => $warung->id,
                'transaction_date' => Carbon::now(),
                'amount' => $transaction->amount,
            ]);
        }
    }
}
