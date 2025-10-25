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
        Schema::create('custodianships', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
            $table->enum('delivery_status', ['pending', 'sent', 'delivered', 'failed', 'bounced'])->nullable();
            $table->string('interval', 20);
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamp('next_trigger_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('last_reset_at');
            $table->index(['status', 'next_trigger_at'], 'idx_active_triggers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custodianships');
    }
};
