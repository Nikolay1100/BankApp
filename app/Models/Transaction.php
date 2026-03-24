<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'sender_account_id',
        'receiver_account_id',
        'amount',
        'type',
        'idempotency_key',
        'ip_address',
        'user_agent',
        'status',
    ];

    public function senderAccount()
    {
        return $this->belongsTo(Account::class , 'sender_account_id');
    }

    public function receiverAccount()
    {
        return $this->belongsTo(Account::class , 'receiver_account_id');
    }
}
