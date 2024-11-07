<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warung extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'location',
    ];


    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function transactionHistory()
    {
        return $this->hasMany(TransactionHistory::class);
    }
}
