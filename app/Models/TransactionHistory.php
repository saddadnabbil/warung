<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'customer_id',
        'warung_id',
        'transaction_date',
        'amount',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function warung()
    {
        return $this->belongsTo(Warung::class);
    }
}
