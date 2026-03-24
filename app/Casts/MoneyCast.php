<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Money\Currency;
use Money\Money;

class MoneyCast implements CastsAttributes
{
      /**
       * Converts the value from the database to a Money object.
       */
      public function get(Model $model, string $key, mixed $value, array $attributes): Money
      {
            $currencyCode = $model->currency ? $model->currency->code : 'USD';
            return new Money((string)$value, new Currency($currencyCode));
      }

      /**
       * Prepares the Money object for saving in the database.
       */
      public function set(Model $model, string $key, mixed $value, array $attributes): int
      {
            if (!$value instanceof Money) {
                  throw new \InvalidArgumentException('The balance must be passed as a Money\Money object.');
            }

            return (int)$value->getAmount();
      }
}
