<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence;

use Src\Catalog\Domain\Model\Course;
use Src\Catalog\Domain\Model\CourseId;
use Src\Catalog\Domain\Repository\CourseRepository;
use Src\Catalog\Infrastructure\Eloquent\CourseEloquentModel;
use Src\Shared\Domain\ValueObjects\Money;

/**
 * EloquentCourseRepository — implementasi Repository Pattern via Eloquent.
 *
 * Tugas utamanya adalah MENERJEMAHKAN antara aggregate domain `Course`
 * dan model persistensi `CourseEloquentModel`. Domain tidak pernah melihat
 * Eloquent; semua konversi terjadi di sini (mapping in/out).
 */
final class EloquentCourseRepository implements CourseRepository
{
    /**
     * Menghasilkan CourseId baru (UUID).
     */
    public function nextIdentity(): CourseId
    {
        return CourseId::generate();
    }

    /**
     * Menyimpan aggregate: buat baru bila belum ada, atau perbarui.
     */
    public function save(Course $course): void
    {
        CourseEloquentModel::query()->updateOrCreate(
            ['id' => $course->id()->value],
            [
                'title' => $course->title(),
                'description' => $course->description(),
                'price_amount' => $course->price()->amount,
                'price_currency' => $course->price()->currency,
                'published' => $course->isPublished(),
            ],
        );
    }

    public function findById(CourseId $id): ?Course
    {
        $model = CourseEloquentModel::query()->find($id->value);

        return $model ? $this->toDomain($model) : null;
    }

    /**
     * @return Course[]
     */
    public function all(): array
    {
        return CourseEloquentModel::query()
            ->latest('created_at')
            ->get()
            ->map(fn (CourseEloquentModel $m) => $this->toDomain($m))
            ->all();
    }

    /**
     * @return Course[]
     */
    public function published(): array
    {
        return CourseEloquentModel::query()
            ->where('published', true)
            ->latest('created_at')
            ->get()
            ->map(fn (CourseEloquentModel $m) => $this->toDomain($m))
            ->all();
    }

    /**
     * Mapping OUT: dari model persistensi menjadi aggregate domain.
     */
    private function toDomain(CourseEloquentModel $model): Course
    {
        return Course::reconstitute(
            CourseId::fromString($model->id),
            $model->title,
            $model->description,
            Money::of($model->price_amount, $model->price_currency),
            $model->published,
        );
    }
}
