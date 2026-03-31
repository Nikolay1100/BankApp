<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => [
                'value' => (int)$this->amount,
                'formatted' => number_format($this->amount / 100, 2),
            ],
            'type' => $this->type,
            'status' => $this->status,

            $this->mergeWhen($this->sender_account_id && $this->receiver_account_id, [
                'sender_name' => $this->senderAccount?->user?->name,
                'receiver_name' => $this->receiverAccount?->user?->name,
            ]),

            'created_at' => $this->created_at,
        ];
    }
}