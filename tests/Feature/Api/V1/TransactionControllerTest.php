<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_deposit_to_own_account()
    {
        $user = \App\Models\User::factory()->create();
        $account = $user->defaultAccount;
        $account->update(['balance' => new \Money\Money(0, new \Money\Currency('USD'))]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/users/{$user->id}/deposit", [
            'amount' => 100.50,
        ]);

        $response->assertStatus(200);
        // Balance returns Money object, so comparison must be with string cents
        $this->assertEquals("10050", $account->fresh()->balance->getAmount());
        $this->assertDatabaseHas('transactions', [
            'receiver_account_id' => $account->id,
            'amount' => 10050,
            'type' => 'deposit',
        ]);
    }

    public function test_user_cannot_deposit_to_others_account()
    {
        $user1 = \App\Models\User::factory()->create();
        $user2 = \App\Models\User::factory()->create();

        $response = $this->actingAs($user1)
            ->postJson("/api/v1/users/{$user2->id}/deposit", [
            'amount' => 1000,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_transfer_money_to_others()
    {
        $sender = \App\Models\User::factory()->create();
        $receiver = \App\Models\User::factory()->create();

        $senderAcc = $sender->defaultAccount;
        $receiverAcc = $receiver->defaultAccount;

        // Give sender some money manually
        $senderAcc->update(['balance' => new \Money\Money(20000, new \Money\Currency('USD'))]);
        $receiverAcc->update(['balance' => new \Money\Money(0, new \Money\Currency('USD'))]);

        $response = $this->actingAs($sender)
            ->postJson("/api/v1/transfers", [
            'receiver_id' => $receiver->id,
            'amount' => 150.75,
        ]);

        $response->assertStatus(200);
        $this->assertEquals("4925", $senderAcc->fresh()->balance->getAmount()); // 20000 - 15075 = 4925
        $this->assertEquals("15075", $receiverAcc->fresh()->balance->getAmount());
    }

    public function test_user_cannot_transfer_more_than_balance()
    {
        $sender = \App\Models\User::factory()->create();
        $receiver = \App\Models\User::factory()->create();
        $senderAcc = $sender->defaultAccount;

        $senderAcc->update(['balance' => new \Money\Money(5000, new \Money\Currency('USD'))]);

        $response = $this->actingAs($sender)
            ->postJson("/api/v1/transfers", [
            'receiver_id' => $receiver->id,
            'amount' => 60,
        ]);

        $response->assertStatus(422); // Our AppException returns 422
        $this->assertEquals("5000", $senderAcc->fresh()->balance->getAmount());
    }

    public function test_user_cannot_transfer_to_self()
    {
        $user = \App\Models\User::factory()->create();
        $account = $user->defaultAccount;
        $account->update(['balance' => new \Money\Money(10000, new \Money\Currency('USD'))]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/transfers", [
            'receiver_id' => $user->id,
            'amount' => 10,
        ]);

        $response->assertStatus(422);
    }
}
