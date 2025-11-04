<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE custodianships MODIFY COLUMN status ENUM('draft', 'active', 'delivering', 'completed') DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("UPDATE custodianships SET status = 'completed' WHERE status = 'delivering'");
        DB::statement("ALTER TABLE custodianships MODIFY COLUMN status ENUM('draft', 'active', 'completed') DEFAULT 'draft'");
    }
};
