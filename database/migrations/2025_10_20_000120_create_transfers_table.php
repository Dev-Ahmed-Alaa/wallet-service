<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_wallet_id')->constrained('wallets')->nullOnDelete();
            $table->foreignId('receiver_wallet_id')->constrained('wallets')->nullOnDelete();
            $table->bigInteger('amount'); // cents
            $table->bigInteger('fee_amount')->default(0); // cents
            $table->string('status')->default('pending'); // pending|succeeded|failed
            $table->string('idempotency_key')->unique();
            $table->string('error')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['sender_wallet_id', 'status']);
            $table->index(['receiver_wallet_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
