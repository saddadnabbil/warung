<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'name',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function warungs()
    {
        return $this->hasMany(Warung::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    public function balance()
    {
        return $this->hasOne(Balance::class, 'buyer_id');
    }

    public function transactionHistory()
    {
        return $this->hasMany(TransactionHistory::class, 'buyer_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid(); // Menetapkan UUID sebagai ID
            }
        });
    }
}
