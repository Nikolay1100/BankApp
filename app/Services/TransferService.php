<?php

namespace App\Services;

use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InvalidTransferException;
use Money\Money;
use App\Exceptions\AppException;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;
use Throwable;

class TransferService
{
    /**
     * Deposit funds to a user account.
     *
     * @param User $user
     * @param Money $amount
     * @param string|null $idempotencyKey
     * @param array $metadata
     * @return Transaction
     * @throws InvalidTransferException
     */
    public function deposit(
        User $user,
        Money $amount,
        ?string $idempotencyKey = null,
        array $metadata = []
    ): Transaction {
        if (!$amount->isPositive()) {
            throw new InvalidTransferException("Amount must be positive.");
        }

        if ($idempotencyKey) {
            $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing)
                return $existing;
        }

        return DB::transaction(function () use ($user, $amount, $idempotencyKey, $metadata) {
            $lockedAccount = clone $user->defaultAccount;

            $lockedAccount = Account::where('id', $lockedAccount->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAccount->balance = $lockedAccount->balance->add($amount);
            $lockedAccount->save();

            return Transaction::create(array_merge([
                'receiver_account_id' => $lockedAccount->id,
                'amount' => $amount->getAmount(),
                'type' => TransactionType::DEPOSIT,
                'status' => TransactionStatus::COMPLETED,
                'idempotency_key' => $idempotencyKey,
            ], $metadata));
        });
    }

    /**
     * Transfer funds between two users.
     *
     * @param User $sender
     * @param User $receiver
     * @param Money $amount
     * @param string|null $idempotencyKey
     * @param array $metadata
     * @return Transaction
     * @throws InsufficientFundsException
     * @throws InvalidTransferException|Throwable
     */
    public function transfer(
        User $sender,
        User $receiver,
        Money $amount,
        ?string $idempotencyKey = null,
        array $metadata = []
    ): Transaction {
        if (!$amount->isPositive()) {
            throw new InvalidTransferException("Amount must be positive.");
        }

        if ($sender->id === $receiver->id) {
            throw new InvalidTransferException("Sender and receiver cannot be the same user.");
        }

        if ($idempotencyKey) {
            $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing)
                return $existing;
        }

        return DB::transaction(function () use ($sender, $receiver, $amount, $idempotencyKey, $metadata) {
            $senderAccountId = $sender->defaultAccount->id;
            $receiverAccountId = $receiver->defaultAccount->id;
            [$firstId, $secondId] = $senderAccountId < $receiverAccountId ? [$senderAccountId, $receiverAccountId] : [$receiverAccountId, $senderAccountId];

            $lockedFirstAcc = Account::where('id', $firstId)->lockForUpdate()->firstOrFail();
            $lockedSecondAcc = Account::where('id', $secondId)->lockForUpdate()->firstOrFail();

            $senderAcc = $lockedFirstAcc->id === $senderAccountId ? $lockedFirstAcc : $lockedSecondAcc;
            $receiverAcc = $lockedFirstAcc->id === $receiverAccountId ? $lockedFirstAcc : $lockedSecondAcc;

            if ($senderAcc->balance->lessThan($amount)) {
                throw new InsufficientFundsException("Insufficient funds.");
            }

            if ($senderAcc->currency_id !== $receiverAcc->currency_id) {
                throw new InvalidTransferException("Currency mismatch between sender and receiver accounts.");
            }

            $senderAcc->balance = $senderAcc->balance->subtract($amount);
            $receiverAcc->balance = $receiverAcc->balance->add($amount);
            $senderAcc->save();
            $receiverAcc->save();

            return Transaction::create(array_merge([
                'sender_account_id' => $senderAcc->id,
                'receiver_account_id' => $receiverAcc->id,
                'amount' => $amount->getAmount(),
                'type' => TransactionType::TRANSFER,
                'status' => TransactionStatus::COMPLETED,
                'idempotency_key' => $idempotencyKey,
            ], $metadata));
        });
    }
}
