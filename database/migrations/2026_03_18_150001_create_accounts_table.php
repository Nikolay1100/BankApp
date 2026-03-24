<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
      public function up(): void
      {
            Schema::create('accounts', function (Blueprint $table) {
                  $table->id();
                  $table->foreignId('user_id')
                        ->constrained('users')
                        ->cascadeOnDelete();
                  $table->foreignId('currency_id')
                        ->constrained('currencies')
                        ->restrictOnDelete();
                  $table->unsignedBigInteger('balance')
                        ->default(0)
                        ->check('balance >= 0');
                  $table->boolean('is_default')->default(false);
                  $table->timestamps();
                  $table->index(['user_id', 'is_default']);
                  $table->unique(['user_id', 'currency_id']);
            });
      }

      public function down(): void
      {
            Schema::dropIfExists('accounts');
      }
};
