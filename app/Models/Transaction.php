<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;

class Transaction extends Model
{
    use HasFactory;

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

    protected $casts = [
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
    ];

    public function senderAccount()
    {
        return $this->belongsTo(Account::class, 'sender_account_id');
    }

    public function receiverAccount()
    {
        return $this->belongsTo(Account::class, 'receiver_account_id');
    }
}