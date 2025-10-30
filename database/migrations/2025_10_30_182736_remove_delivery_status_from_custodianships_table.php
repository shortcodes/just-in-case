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
        Schema::table('custodianships', function (Blueprint $table) {
            $table->dropColumn('delivery_status');
        });
    }

    public function down(): void
    {
        Schema::table('custodianships', function (Blueprint $table) {
            $table->enum('delivery_status', ['pending', 'sent', 'delivered', 'failed', 'bounced'])->nullable()->after('status');
        });
    }
};
