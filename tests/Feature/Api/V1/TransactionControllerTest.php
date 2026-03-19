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
        $user = \App\Models\User::factory()->create(['balance' => 0]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/users/{$user->id}/deposit", [
            'amount' => 100.50,
        ]);

        $response->assertStatus(201);
        $this->assertEquals(10050, $user->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'receiver_id' => $user->id,
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
        $sender = \App\Models\User::factory()->create(['balance' => 20000]); // 200.00
        $receiver = \App\Models\User::factory()->create(['balance' => 0]);

        $response = $this->actingAs($sender)
            ->postJson("/api/v1/transfers", [
            'receiver_id' => $receiver->id,
            'amount' => 150.75,
        ]);

        $response->assertStatus(201);
        $this->assertEquals(4925, $sender->fresh()->balance); // 20000 - 15075 = 4925
        $this->assertEquals(15075, $receiver->fresh()->balance);
    }

    public function test_user_cannot_transfer_more_than_balance()
    {
        $sender = \App\Models\User::factory()->create(['balance' => 5000]); // 50.00
        $receiver = \App\Models\User::factory()->create();

        $response = $this->actingAs($sender)
            ->postJson("/api/v1/transfers", [
            'receiver_id' => $receiver->id,
            'amount' => 60,
        ]);

        $response->assertStatus(400); // Bad request from TransferService exception
        $this->assertEquals(5000, $sender->fresh()->balance);
    }

    public function test_user_cannot_transfer_to_self()
    {
        $user = \App\Models\User::factory()->create(['balance' => 10000]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/transfers", [
            'receiver_id' => $user->id,
            'amount' => 10,
        ]);

        $response->assertStatus(400);
    }
}
