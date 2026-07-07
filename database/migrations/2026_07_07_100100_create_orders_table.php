<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel penyimpanan untuk aggregate Order (Ordering context).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Referensi lintas-context disimpan sebagai id mentah (bukan FK keras)
            // untuk menjaga kelonggaran antar bounded context.
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('course_id');
            // Snapshot judul & harga saat order dibuat (harga terkunci).
            $table->string('course_title');
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('IDR');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
