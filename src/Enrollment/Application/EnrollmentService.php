<?php

declare(strict_types=1);

namespace Src\Enrollment\Application;

use Src\Enrollment\Domain\Model\Enrollment;
use Src\Enrollment\Domain\Model\EnrollmentId;
use Src\Enrollment\Domain\Repository\EnrollmentRepository;

/**
 * EnrollmentService — Application Service untuk Enrollment context.
 */
final readonly class EnrollmentService
{
    public function __construct(
        private EnrollmentRepository $enrollments,
    ) {}

    /**
     * Use case: memberi mahasiswa akses ke kursus (idempoten).
     *
     * Bila akses sudah ada, tidak melakukan apa-apa — mencegah duplikat
     * bila event diproses lebih dari sekali.
     */
    public function grantAccess(int $studentId, string $courseId): void
    {
        if ($this->enrollments->exists($studentId, $courseId)) {
            return;
        }

        $this->enrollments->save(
            Enrollment::grant(EnrollmentId::generate(), $studentId, $courseId)
        );
    }

    /**
     * Query: apakah mahasiswa boleh mengakses kursus tertentu.
     */
    public function canAccess(int $studentId, string $courseId): bool
    {
        return $this->enrollments->exists($studentId, $courseId);
    }

    /**
     * Query: id kursus yang bisa diakses mahasiswa.
     *
     * @return string[]
     */
    public function accessibleCourseIds(int $studentId): array
    {
        return $this->enrollments->courseIdsForStudent($studentId);
    }
}
