<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'warung_id',
        'buyer_id',
        'transaction_type',
        'amount',
        'description',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($transaction) {
            if ($transaction->transaction_type === 'deposit') {
                $transaction->updateBuyerBalance();
            }
        });
    }

    public function updateBuyerBalance()
    {
        $balance = $this->buyer->balance;

        if ($balance) {
            // Jika balance sudah ada, tambahkan jumlah deposit
            $balance->increment('balance', $this->amount);
        } else {
            // Jika balance belum ada, buat baru dengan nilai deposit
            $this->buyer->balance()->create(['balance' => $this->amount]);
        }
    }

    public function warung()
    {
        return $this->belongsTo(Warung::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'pembeli');
            });
    }

    public function transactionHistory()
    {
        return $this->hasMany(TransactionHistory::class);
    }
}
