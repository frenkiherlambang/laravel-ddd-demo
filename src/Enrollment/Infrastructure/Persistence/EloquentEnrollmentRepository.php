<?php

declare(strict_types=1);

namespace Src\Enrollment\Infrastructure\Persistence;

use Src\Enrollment\Domain\Model\Enrollment;
use Src\Enrollment\Domain\Repository\EnrollmentRepository;
use Src\Enrollment\Infrastructure\Eloquent\EnrollmentEloquentModel;

/**
 * EloquentEnrollmentRepository — implementasi Repository Pattern Enrollment.
 */
final class EloquentEnrollmentRepository implements EnrollmentRepository
{
    public function save(Enrollment $enrollment): void
    {
        EnrollmentEloquentModel::query()->updateOrCreate(
            [
                'student_id' => $enrollment->studentId(),
                'course_id' => $enrollment->courseId(),
            ],
            ['id' => $enrollment->id()->value],
        );
    }

    public function exists(int $studentId, string $courseId): bool
    {
        return EnrollmentEloquentModel::query()
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->exists();
    }

    /**
     * @return string[]
     */
    public function courseIdsForStudent(int $studentId): array
    {
        return EnrollmentEloquentModel::query()
            ->where('student_id', $studentId)
            ->pluck('course_id')
            ->all();
    }
}
