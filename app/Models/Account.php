<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Casts\MoneyCast;

class Account extends Model
{
      protected $fillable = [
            'user_id',
            'currency_id',
            'balance',
            'is_default',
      ];

      protected $with = ['currency'];

      protected function casts(): array
      {
            return [
                  'balance' => MoneyCast::class ,
            ];
      }

      public function user(): BelongsTo
      {
            return $this->belongsTo(User::class);
      }

      public function currency(): BelongsTo
      {
            return $this->belongsTo(Currency::class);
      }
}
