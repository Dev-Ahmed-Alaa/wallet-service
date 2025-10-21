<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->string('direction'); // credit|debit
            $table->string('type'); // deposit|withdrawal|transfer_in|transfer_out|fee
            $table->bigInteger('amount'); // cents, always positive
            $table->bigInteger('balance_after'); // cents
            $table->nullableMorphs('reference');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at'); // immutable one-way ledger

            $table->index(['wallet_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
