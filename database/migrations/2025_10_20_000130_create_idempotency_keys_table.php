<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('scope')->nullable(); // e.g., transfer
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('request_hash')->nullable();
            $table->string('response_hash')->nullable();
            $table->json('response_body')->nullable();
            $table->string('status')->default('pending'); // pending|completed|failed
            $table->timestamp('created_at'); // one-way idempotency key

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
