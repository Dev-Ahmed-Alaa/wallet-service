<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->bigInteger('balance'); // cents
            $table->timestamp('snapshot_taken_at');
            $table->timestamps();

            $table->index(['wallet_id', 'snapshot_taken_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_snapshots');
    }
};
