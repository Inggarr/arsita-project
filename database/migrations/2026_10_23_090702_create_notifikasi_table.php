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
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->integer('user_id'); // Penerima notifikasi (sesuai users.id_user int)
            $table->integer('sender_id')->nullable(); // Pengirim / pimpinan
            $table->string('judul', 150);
            $table->text('pesan');
            $table->string('url', 255)->nullable(); // URL target saat diklik
            $table->string('tipe', 30)->default('info'); // revisi, info, warning, success
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id_user')->on('users')->cascadeOnDelete();
            $table->foreign('sender_id')->references('id_user')->on('users')->nullOnDelete();
            $table->index(['user_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
