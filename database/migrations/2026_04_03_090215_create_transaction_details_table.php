<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();

            // Security metadata
            $table->ipAddress('ip_address');
            $table->text('user_agent');
            $table->string('device_fingerprint')->nullable();

            // Business metadata
            $table->string('location')->nullable();
            $table->string('description')->nullable();
            $table->string('external_reference')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
