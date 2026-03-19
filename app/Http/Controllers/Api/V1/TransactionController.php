<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $transferService;

    public function __construct(\App\Services\TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function deposit(\App\Http\Requests\DepositRequest $request, \App\Models\User $user)
    {
        try {
            $amountInCents = (int)round($request->validated('amount') * 100);
            $transaction = $this->transferService->deposit($user, $amountInCents);

            return response()->json($transaction, 201);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function transfer(\App\Http\Requests\TransferRequest $request)
    {
        try {
            $sender = \App\Models\User::findOrFail($request->validated('sender_id'));
            $receiver = \App\Models\User::findOrFail($request->validated('receiver_id'));
            $amountInCents = (int)round($request->validated('amount') * 100);

            $transaction = $this->transferService->transfer($sender, $receiver, $amountInCents);

            return response()->json($transaction, 201);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
