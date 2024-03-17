<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminReceiveTokenTransactionHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'unique_code',
        'deposit_id',
        'amount',
        'fees',
        'to_address',
        'from_address',
        'transaction_hash',
        'status',
        'type'
    ];
}
