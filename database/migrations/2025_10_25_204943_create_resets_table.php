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
        Schema::create('resets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custodianship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('reset_method', ['manual_button', 'post_edit_modal']);
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->timestamp('created_at');

            $table->index(['custodianship_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resets');
    }
};
