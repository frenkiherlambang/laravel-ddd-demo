<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Catalog\Application\CourseService;

/**
 * CourseSeeder — mengisi katalog kursus MELALUI domain (Application Service).
 *
 * Sengaja TIDAK menyentuh Eloquent langsung: seeding pun menghormati alur
 * domain (Course::create -> repository->save) sehingga invariant tetap berlaku.
 */
class CourseSeeder extends Seeder
{
    public function __construct(
        private readonly CourseService $courses,
    ) {}

    public function run(): void
    {
        // Daftar kursus contoh: [judul, deskripsi, harga (rupiah)].
        $catalog = [
            ['Dasar Pemrograman PHP', 'Belajar sintaks PHP, tipe data, kontrol alur, dan fungsi dari nol.', 150000],
            ['Laravel untuk Pemula', 'Membangun aplikasi web pertama Anda dengan Laravel: routing, blade, dan Eloquent.', 350000],
            ['Domain-Driven Design Praktis', 'Menerapkan DDD, layered architecture, dan bounded context pada proyek nyata.', 500000],
            ['Event Sourcing & CQRS', 'Memahami event sourcing, aggregate, projector, dan reactor dengan Spatie.', 450000],
            ['Integrasi Payment Gateway', 'Pola Anti-Corruption Layer untuk integrasi ke gateway pembayaran seperti DOKU.', 300000],
        ];

        foreach ($catalog as [$title, $description, $price]) {
            // Use case domain: buat kursus dan langsung publikasikan ke katalog.
            $this->courses->createCourse(
                title: $title,
                description: $description,
                priceAmount: $price,
                publish: true,
            );
        }
    }
}
