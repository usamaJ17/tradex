<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateGasFeesTransactionHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'unique_code',
        'wallet_id',
        'deposit_id',
        'amount',
        'fees',
        'coin_type',
        'admin_address',
        'user_address',
        'transaction_hash',
        'status',
        'type'
    ];
}
