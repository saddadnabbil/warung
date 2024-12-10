<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'warung_id',
        'customer_id',
        'transaction_type',
        'amount',
        'description',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($transaction) {
            $transaction->updateBuyerBalance();
        });

        static::updated(function ($transaction) {
            $transaction->updateBuyerBalance();
        });

        static::deleted(function ($transaction) {
            $transaction->updateBuyerBalance(true); // Handle rollback on delete
        });
    }

    public function updateBuyerBalance($isDeleting = false)
    {
        DB::transaction(function () use ($isDeleting) {
            $balance = Balance::firstOrNew(['customer_id' => $this->customer_id]);

            if ($isDeleting) {
                // Rollback the transaction's effect on the balance
                if ($this->transaction_type == 'deposit') {
                    $balance->balance -= $this->amount;
                } elseif ($this->transaction_type == 'purchase') {
                    $balance->balance += $this->amount;
                }
            } else {
                // Apply the transaction's effect on the balance
                if ($this->transaction_type == 'deposit') {
                    $balance->balance += $this->amount;
                } elseif ($this->transaction_type == 'purchase') {
                    $balance->balance -= $this->amount;
                }
            }

            $balance->save();
        });
    }


    public function warung()
    {
        return $this->belongsTo(Warung::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function transactionHistory()
    {
        return $this->hasMany(TransactionHistory::class);
    }
}
