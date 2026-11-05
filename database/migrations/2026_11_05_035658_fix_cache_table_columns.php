<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('cache', 'k') && ! Schema::hasColumn('cache', 'key')) {
            DB::statement('ALTER TABLE `cache` CHANGE COLUMN `k` `key` VARCHAR(255) NOT NULL');
        }

        if (Schema::hasColumn('cache', 'v') && ! Schema::hasColumn('cache', 'value')) {
            DB::statement('ALTER TABLE `cache` CHANGE COLUMN `v` `value` MEDIUMTEXT NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cache', 'key') && ! Schema::hasColumn('cache', 'k')) {
            DB::statement('ALTER TABLE `cache` CHANGE COLUMN `key` `k` VARCHAR(255) NOT NULL');
        }

        if (Schema::hasColumn('cache', 'value') && ! Schema::hasColumn('cache', 'v')) {
            DB::statement('ALTER TABLE `cache` CHANGE COLUMN `value` `v` MEDIUMTEXT NOT NULL');
        }
    }
};
