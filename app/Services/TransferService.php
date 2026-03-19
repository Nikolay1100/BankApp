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
      public function deposit(User $user, int $amount): Transaction
      {
            if ($amount <= 0) {
                  throw new Exception("Amount must be positive.");
            }

            return DB::transaction(function () use ($user, $amount) {
                  // Locking the user record to prevent race conditions during update
                  $user = User::where('id', $user->id)->lockForUpdate()->first();

                  $user->increment('balance', $amount);

                  return Transaction::create([
                        'receiver_id' => $user->id,
                        'amount' => $amount,
                        'type' => 'deposit',
                  ]);
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
      public function transfer(User $sender, User $receiver, int $amount): Transaction
      {
            if ($amount <= 0) {
                  throw new Exception("Amount must be positive.");
            }

            if ($sender->id === $receiver->id) {
                  throw new Exception("Sender and receiver cannot be the same user.");
            }

            return DB::transaction(function () use ($sender, $receiver, $amount) {
                  // Lock rows to prevent race conditions. 
                  // Better to lock in a consistent order (e.g. by ID) to prevent deadlocks.
                  $first = $sender->id < $receiver->id ? $sender : $receiver;
                  $second = $sender->id < $receiver->id ? $receiver : $sender;

                  User::where('id', $first->id)->lockForUpdate()->first();
                  User::where('id', $second->id)->lockForUpdate()->first();

                  // Refetch current state after lock
                  $sender = $sender->fresh();
                  $receiver = $receiver->fresh();

                  if ($sender->balance < $amount) {
                        throw new Exception("Insufficient funds.");
                  }

                  $sender->decrement('balance', $amount);
                  $receiver->increment('balance', $amount);

                  return Transaction::create([
                        'sender_id' => $sender->id,
                        'receiver_id' => $receiver->id,
                        'amount' => $amount,
                        'type' => 'transfer',
                  ]);
            });
      }
}
