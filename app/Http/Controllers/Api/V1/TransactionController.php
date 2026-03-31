<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TransferService;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Responses\V1\Transaction\DepositResponse;
use App\Http\Responses\V1\Transaction\TransferResponse;

class TransactionController extends Controller
{
    protected $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * Handles funds deposit to the user's default account.
     */
    public function deposit(DepositRequest $request, User $user): DepositResponse
    {
        $transaction = $this->transferService->deposit(
            $user,
            $request->getMoney(),
            $request->header('Idempotency-Key'),
        ['ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]
        );

        return new DepositResponse($transaction);
    }

    /**
     * Handles funds transfer between users' default accounts.
     */
    public function transfer(TransferRequest $request): TransferResponse
    {
        $transaction = $this->transferService->transfer(
            $request->user(),
            $request->receiver(),
            $request->getMoney(),
            $request->header('Idempotency-Key'),
        ['ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]
        );

        return new TransferResponse($transaction);
    }
}
