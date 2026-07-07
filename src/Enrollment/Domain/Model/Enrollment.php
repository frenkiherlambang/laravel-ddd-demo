<?php

declare(strict_types=1);

namespace Src\Enrollment\Domain\Model;

/**
 * Enrollment — Aggregate Root pada Enrollment bounded context.
 *
 * Merepresentasikan hak akses seorang mahasiswa ke sebuah kursus setelah
 * pembayaran lunas. Dibuat sebagai reaksi atas event InvoicePaid (via Reactor),
 * sehingga context ini terpisah rapi dari proses pembayaran.
 */
final class Enrollment
{
    private function __construct(
        private readonly EnrollmentId $id,
        private readonly int $studentId,
        private readonly string $courseId,
    ) {}

    /**
     * Factory: memberi mahasiswa akses ke kursus.
     */
    public static function grant(EnrollmentId $id, int $studentId, string $courseId): self
    {
        return new self($id, $studentId, $courseId);
    }

    /**
     * Factory: rekonstruksi dari penyimpanan.
     */
    public static function reconstitute(EnrollmentId $id, int $studentId, string $courseId): self
    {
        return new self($id, $studentId, $courseId);
    }

    public function id(): EnrollmentId
    {
        return $this->id;
    }

    public function studentId(): int
    {
        return $this->studentId;
    }

    public function courseId(): string
    {
        return $this->courseId;
    }
}
