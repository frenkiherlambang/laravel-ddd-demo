<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel penyimpanan read/write untuk aggregate Course (Catalog context).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            // Menggunakan UUID sebagai primary key agar selaras dengan CourseId domain.
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description');
            // Harga disimpan sebagai integer (rupiah penuh), sesuai Money VO.
            $table->unsignedBigInteger('price_amount');
            $table->string('price_currency', 3)->default('IDR');
            $table->boolean('published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
