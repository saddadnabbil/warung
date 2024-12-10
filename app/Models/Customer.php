<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'user_id',
        'warung_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warung()
    {
        return $this->belongsTo(Warung::class, 'warung_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    public function balance()
    {
        return $this->hasOne(Balance::class, 'customer_id');
    }

    public function transactionHistory()
    {
        return $this->hasMany(TransactionHistory::class, 'customer_id');
    }
}
