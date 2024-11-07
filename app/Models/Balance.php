<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'balance',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'pembeli');
            });
    }
}
