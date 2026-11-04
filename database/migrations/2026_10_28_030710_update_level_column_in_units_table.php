<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE units MODIFY COLUMN level ENUM('rektorat', 'fakultas', 'prodi', 'lembaga', 'upt', 'pusat') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE units MODIFY COLUMN level ENUM('rektorat', 'fakultas', 'prodi') NOT NULL");
        }
    }
};
