<?php

namespace App\Http\Requests\Transactions;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Money\Currency;
use Money\Money;

class TransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'receiver_id' => [
                'required',
                'exists:users,id',
                \Illuminate\Validation\Rule::notIn([$this->user()->id])
            ],
            'amount' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.not_in' => 'You cannot transfer money to yourself.',
        ];
    }

    /**
     * Converts the amount from the request to a Money object based on the user's account currency.
     */
    public function getMoney(): Money
    {
        $amountInCents = (int)round($this->validated('amount') * 100);
        $currencyCode = $this->user()->defaultAccount->currency->code ?? 'USD';

        return new Money((string)$amountInCents, new Currency($currencyCode));
    }

    /**
     * Returns the receiver model.
     */
    public function receiver(): User
    {
        return User::findOrFail($this->validated('receiver_id'));
    }
}
