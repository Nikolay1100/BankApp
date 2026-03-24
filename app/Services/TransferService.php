<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Exception;

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
      public function deposit(User $user, int $amount, ?string $idempotencyKey = null, array $metadata = []): Transaction
      {
            if ($amount <= 0) {
                  throw new Exception("Amount must be positive.");
            }

            if ($idempotencyKey) {
                  $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
                  if ($existing)
                        return $existing;
            }

            return DB::transaction(function () use ($user, $amount, $idempotencyKey, $metadata) {
                  // Locking the user record to prevent race conditions during update
                  $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

                  $lockedUser->balance += $amount;
                  $lockedUser->save();

                  return Transaction::create(array_merge([
                        'receiver_id' => $lockedUser->id,
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
      public function transfer(User $sender, User $receiver, int $amount, ?string $idempotencyKey = null, array $metadata = []): Transaction
      {
            if ($amount <= 0) {
                  throw new Exception("Amount must be positive.");
            }

            if ($sender->id === $receiver->id) {
                  throw new Exception("Sender and receiver cannot be the same user.");
            }

            if ($idempotencyKey) {
                  $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
                  if ($existing)
                        return $existing;
            }

            return DB::transaction(function () use ($sender, $receiver, $amount, $idempotencyKey, $metadata) {

                  // Lock rows to prevent race conditions (locking in consistent order to avoid deadlocks)
                  $senderId = $sender->id;
                  $receiverId = $receiver->id;

                  [$firstId, $secondId] = $senderId < $receiverId ? [$senderId, $receiverId] : [$receiverId, $senderId];

                  $lockedFirstUser = User::where('id', $firstId)->lockForUpdate()->first();
                  $lockedSecondUser = User::where('id', $secondId)->lockForUpdate()->first();

                  // Identify which locked record is sender and which is receiver
                  $sender = $lockedFirstUser->id === $senderId ? $lockedFirstUser : $lockedSecondUser;
                  $receiver = $lockedFirstUser->id === $receiverId ? $lockedFirstUser : $lockedSecondUser;

                  if ($sender->balance < $amount) {
                        throw new Exception("Insufficient funds.");
                  }

                  $sender->decrement('balance', $amount);
                  $receiver->increment('balance', $amount);

                  return Transaction::create(array_merge([
                        'sender_id' => $sender->id,
                        'receiver_id' => $receiver->id,
                        'amount' => $amount,
                        'type' => 'transfer',
                        'idempotency_key' => $idempotencyKey,
                  ], $metadata));
            });
      }
}
