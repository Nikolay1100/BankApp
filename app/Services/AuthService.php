<?php

namespace App\Services;

use App\Models\User;
use App\Models\Currency;
use Illuminate\Support\Facades\Hash;
use Money\Money;
use Money\Currency as MoneyCurrency;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'age' => $data['age'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        $usd = Currency::firstOrCreate(
            ['code' => 'USD'],
            ['name' => 'US Dollar', 'symbol' => '$']
        );

        $user->accounts()->create([
            'currency_id' => $usd->id,
            'balance' => new Money('0', new MoneyCurrency('USD')),
            'is_default' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [$user, $token];
    }

    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid login details.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [$user, $token];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}