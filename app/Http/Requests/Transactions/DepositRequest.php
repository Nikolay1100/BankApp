<?php

namespace App\Http\Requests\Transactions;

use Illuminate\Foundation\Http\FormRequest;
use Money\Currency;
use Money\Money;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
        ];
    }

    public function getMoney(): Money
    {
        $amountInCents = (int)round($this->validated('amount') * 100);
        $currencyCode = $this->user()->defaultAccount->currency->code ?? 'USD';

        return new Money((string)$amountInCents, new Currency($currencyCode));
    }
}
