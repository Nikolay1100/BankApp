<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InvalidTransferException;

class TransferService
{
      /**
       * Deposit funds to a user account.
       *
       * @param User $user
       * @param int $amount Amount in cents.
       * @return Transaction
       * @throws Exception
       */
      public function deposit(
            User $user,
            int $amount,
            ?string $idempotencyKey = null,
            array $metadata = []
            ): Transaction
      {

            if ($amount <= 0) {
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

                  $lockedAccount->balance += $amount;
                  $lockedAccount->save();

                  return Transaction::create(array_merge([
                        'receiver_account_id' => $lockedAccount->id,
                        'amount' => $amount,
                        'type' => 'deposit',
                        'idempotency_key' => $idempotencyKey,
                  ], $metadata));
            });
      }

      /**
       * Transfer funds between two users.
       *
       * @param User $sender
       * @param User $receiver
       * @param int $amount Amount in cents.
       * @return Transaction
       * @throws Exception
       */
      public function transfer(
            User $sender,
            User $receiver,
            int $amount,
            ?string $idempotencyKey = null,
            array $metadata = []
            ): Transaction
      {

            if ($amount <= 0) {
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

                  if ($senderAcc->balance < $amount) {
                        throw new InsufficientFundsException("Insufficient funds.");
                  }

                  $senderAcc->decrement('balance', $amount);
                  $receiverAcc->increment('balance', $amount);

                  return Transaction::create(array_merge([
                        'sender_account_id' => $senderAcc->id,
                        'receiver_account_id' => $receiverAcc->id,
                        'amount' => $amount,
                        'type' => 'transfer',
                        'idempotency_key' => $idempotencyKey,
                  ], $metadata));
            });
      }
}
