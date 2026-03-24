<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Currency::firstOrCreate(['code' => 'USD'], ['name' => 'US Dollar', 'symbol' => '$']);
        Currency::firstOrCreate(['code' => 'EUR'], ['name' => 'Euro', 'symbol' => '€']);
        Currency::firstOrCreate(['code' => 'RUB'], ['name' => 'Russian Ruble', 'symbol' => '₽']);

        User::factory(10)->create();
    }
}
