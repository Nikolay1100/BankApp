<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InvalidTransferException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transactions\DepositRequest;
use App\Http\Requests\Transactions\TransferRequest;
use App\Http\Responses\V1\Transaction\DepositResponse;
use App\Http\Responses\V1\Transaction\TransferResponse;
use App\Models\User;
use App\Services\TransferService;

class TransactionController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * Handles funds deposit to the user's default account.
     * @throws InvalidTransferException
     */
    public function deposit(DepositRequest $request, User $user): DepositResponse
    {
        $transaction = $this->transferService->deposit(
            $user,
            $request->getMoney(),
            $request->header('Idempotency-Key'),
            metadata: [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_fingerprint' => $request->header('X-Device-Fingerprint')
            ]
        );

        return new DepositResponse($transaction);
    }

    /**
     * Handles funds transfer between users' default accounts.
     *
     * @throws InsufficientFundsException
     * @throws InvalidTransferException
     */
    public function transfer(TransferRequest $request): TransferResponse
    {
        $transaction = $this->transferService->transfer(
            $request->user(),
            $request->receiver(),
            $request->getMoney(),
            $request->header('Idempotency-Key'),
            metadata: [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_fingerprint' => $request->header('X-Device-Fingerprint')
            ]
        );

        return new TransferResponse($transaction);
    }
}
