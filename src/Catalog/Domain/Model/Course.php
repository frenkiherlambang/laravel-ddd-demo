<?php

declare(strict_types=1);

namespace Src\Catalog\Domain\Model;

use InvalidArgumentException;
use Src\Shared\Domain\ValueObjects\Money;

/**
 * Course — Aggregate Root pada Catalog bounded context.
 *
 * Mewakili sebuah kursus yang dibuat Admin dan ditampilkan di katalog.
 * Semua invariant (aturan bisnis) terkait kursus dijaga di sini, BUKAN
 * di controller atau di model Eloquent. Ini objek domain murni: tidak
 * mewarisi Eloquent dan tidak tahu apa pun soal database.
 */
final class Course
{
    /**
     * @param  CourseId  $id  Identitas unik kursus.
     * @param  string  $title  Judul kursus.
     * @param  string  $description  Deskripsi kursus.
     * @param  Money  $price  Harga kursus (Value Object).
     * @param  bool  $published  Apakah kursus tampil di katalog publik.
     */
    private function __construct(
        private readonly CourseId $id,
        private string $title,
        private string $description,
        private Money $price,
        private bool $published,
    ) {
        // Invariant: judul wajib ada.
        if (trim($title) === '') {
            throw new InvalidArgumentException('Judul kursus wajib diisi.');
        }
    }

    /**
     * Factory: membuat kursus baru (default belum dipublikasikan).
     * Digunakan Admin saat "Bikin Kursus".
     */
    public static function create(
        CourseId $id,
        string $title,
        string $description,
        Money $price,
    ): self {
        return new self($id, $title, $description, $price, false);
    }

    /**
     * Factory: merekonstruksi Course dari penyimpanan (dipakai repository).
     */
    public static function reconstitute(
        CourseId $id,
        string $title,
        string $description,
        Money $price,
        bool $published,
    ): self {
        return new self($id, $title, $description, $price, $published);
    }

    /**
     * Mengubah detail kursus. Invariant judul tetap dijaga.
     */
    public function updateDetails(string $title, string $description, Money $price): void
    {
        if (trim($title) === '') {
            throw new InvalidArgumentException('Judul kursus wajib diisi.');
        }

        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
    }

    /**
     * Mempublikasikan kursus agar tampil di katalog mahasiswa.
     */
    public function publish(): void
    {
        $this->published = true;
    }

    /**
     * Menarik kursus dari katalog publik.
     */
    public function unpublish(): void
    {
        $this->published = false;
    }

    public function id(): CourseId
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }
}
