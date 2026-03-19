<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_own_profile()
    {
        $user = \App\Models\User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)
            ->patchJson("/api/v1/users/{$user->id}", [
            'name' => 'New Name',
            'email' => $user->email,
            'age' => 30,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('New Name', $user->fresh()->name);
    }

    public function test_user_cannot_update_other_profile()
    {
        $user1 = \App\Models\User::factory()->create();
        $user2 = \App\Models\User::factory()->create(['name' => 'Target Name']);

        $response = $this->actingAs($user1)
            ->patchJson("/api/v1/users/{$user2->id}", [
            'name' => 'Hacker attempt',
            'email' => 'hacker@example.com',
            'age' => 99,
        ]);

        $response->assertStatus(403);
        $this->assertEquals('Target Name', $user2->fresh()->name);
    }

    public function test_guest_cannot_update_profiles()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->patchJson("/api/v1/users/{$user->id}", [
            'name' => 'Should fail',
        ]);

        $response->assertStatus(401);
    }
}
