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
        Schema::table('deliveries', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempt_number')->default(1)->after('status');
            $table->unsignedTinyInteger('max_attempts')->default(3)->after('attempt_number');
            $table->timestamp('last_retry_at')->nullable()->after('max_attempts');
            $table->timestamp('next_retry_at')->nullable()->after('last_retry_at');
            $table->text('error_message')->nullable()->after('next_retry_at');

            $table->index(['status', 'next_retry_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropIndex(['status', 'next_retry_at']);
            $table->dropColumn(['attempt_number', 'max_attempts', 'last_retry_at', 'next_retry_at', 'error_message']);
        });
    }
};
