<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
      protected $fillable = [
            'user_id',
            'currency_id',
            'balance',
            'is_default',
      ];

      public function user()
      {
            return $this->belongsTo(User::class);
      }

      public function currency()
      {
            return $this->belongsTo(Currency::class);
      }
}
