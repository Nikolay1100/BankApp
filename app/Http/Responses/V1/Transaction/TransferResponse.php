<?php

namespace App\Http\Responses\V1\Transaction;

use Illuminate\Contracts\Support\Responsable;
use App\Http\Resources\V1\TransactionResource;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;

class TransferResponse implements Responsable
{
    public function __construct(
        private $transaction
    ) {}

    public function toResponse($request): JsonResponse
    {
        return app(ResponseService::class)->success(
            new TransactionResource($this->transaction->load(['senderAccount.user', 'receiverAccount.user'])),
            'Transfer successful'
        );
    }
}