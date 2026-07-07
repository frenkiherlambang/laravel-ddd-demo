<?php

declare(strict_types=1);

namespace Src\Catalog\Application;

use Src\Catalog\Domain\Model\Course;
use Src\Catalog\Domain\Model\CourseId;
use Src\Catalog\Domain\Repository\CourseRepository;
use Src\Shared\Domain\ValueObjects\Money;

/**
 * CourseService — Application Service (use case) untuk Catalog context.
 *
 * Layer Application mengorkestrasi alur: menerima input sederhana dari
 * Presentation (controller), memanggil domain + repository, tanpa memuat
 * logika bisnis inti (yang tetap tinggal di aggregate Course).
 */
final readonly class CourseService
{
    public function __construct(
        private CourseRepository $courses,
    ) {}

    /**
     * Use case: Admin membuat kursus baru.
     */
    public function createCourse(
        string $title,
        string $description,
        int $priceAmount,
        bool $publish = true,
    ): CourseId {
        $course = Course::create(
            $this->courses->nextIdentity(),
            $title,
            $description,
            Money::idr($priceAmount),
        );

        // Umumnya kursus baru langsung dipublikasikan agar tampil di katalog.
        if ($publish) {
            $course->publish();
        }

        $this->courses->save($course);

        return $course->id();
    }

    /**
     * Use case: Admin memperbarui kursus.
     */
    public function updateCourse(
        string $courseId,
        string $title,
        string $description,
        int $priceAmount,
        bool $published,
    ): void {
        $course = $this->courses->findById(CourseId::fromString($courseId));

        if ($course === null) {
            return;
        }

        $course->updateDetails($title, $description, Money::idr($priceAmount));

        // Sinkronkan status publikasi sesuai input admin.
        $published ? $course->publish() : $course->unpublish();

        $this->courses->save($course);
    }

    /**
     * Query: seluruh kursus untuk panel admin.
     *
     * @return Course[]
     */
    public function allForAdmin(): array
    {
        return $this->courses->all();
    }

    /**
     * Query: kursus published untuk katalog mahasiswa.
     *
     * @return Course[]
     */
    public function catalog(): array
    {
        return $this->courses->published();
    }

    /**
     * Query: satu kursus berdasarkan id.
     */
    public function find(string $courseId): ?Course
    {
        return $this->courses->findById(CourseId::fromString($courseId));
    }
}
