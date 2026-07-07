<?php

declare(strict_types=1);

namespace Src\Enrollment\Domain\Repository;

use Src\Enrollment\Domain\Model\Enrollment;

/**
 * EnrollmentRepository — kontrak Repository Pattern untuk Enrollment.
 */
interface EnrollmentRepository
{
    public function save(Enrollment $enrollment): void;

    /**
     * Apakah mahasiswa sudah punya akses ke kursus (mencegah duplikat).
     */
    public function exists(int $studentId, string $courseId): bool;

    /**
     * Daftar id kursus yang bisa diakses seorang mahasiswa.
     *
     * @return string[]
     */
    public function courseIdsForStudent(int $studentId): array;
}
