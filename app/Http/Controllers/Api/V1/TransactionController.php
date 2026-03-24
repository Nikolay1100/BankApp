<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TransferService;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Models\User;

class TransactionController extends Controller
{
    protected $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function deposit(DepositRequest $request, User $user)
    {
        if ($user->id !== $request->user()->id) {
            return response()->json(['error' => 'You can only deposit funds to your own account.'], 403);
        }

        $transaction = $this->transferService->deposit(
            $user,
            $request->getMoney(),
            $request->header('Idempotency-Key'),
        ['ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]
        );

        return response()->json($transaction, 201);
    }

    public function transfer(TransferRequest $request)
    {
        $transaction = $this->transferService->transfer(
            $request->user(),
            $request->receiver(),
            $request->getMoney(),
            $request->header('Idempotency-Key'),
        ['ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]
        );

        return response()->json($transaction, 201);
    }
}
