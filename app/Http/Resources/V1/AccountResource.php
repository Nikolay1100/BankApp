<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'balance' => [
                'amount' => (int)$this->balance->getAmount(),
                'formatted' => optional($this->currency)->symbol . number_format($this->balance->getAmount() / 100, 2),
            ],
            'is_default' => (bool)$this->is_default,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'created_at' => $this->created_at,
        ];
    }
}