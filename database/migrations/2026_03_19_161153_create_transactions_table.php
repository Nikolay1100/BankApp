<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_account_id')
                ->nullable()
                ->constrained('accounts');
            $table->foreignId('receiver_account_id')
                ->nullable()
                ->constrained('accounts');
            $table->unsignedBigInteger('amount');
            $table->string('type');
            $table->string('idempotency_key', 64)
                ->nullable()
                ->unique()
                ->index();
            $table->string('status')
                ->default('completed')
                ->index();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
