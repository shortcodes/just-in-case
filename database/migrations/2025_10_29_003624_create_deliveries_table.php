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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custodianship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_email');
            $table->string('mailgun_message_id')->nullable()->index();
            $table->enum('status', ['pending', 'delivered', 'failed'])->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['custodianship_id', 'recipient_id', 'status'], 'deliveries_recipient_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
