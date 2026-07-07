<?php

declare(strict_types=1);

namespace Src\Catalog\Domain\Repository;

use Src\Catalog\Domain\Model\Course;
use Src\Catalog\Domain\Model\CourseId;

/**
 * CourseRepository — kontrak Repository Pattern untuk aggregate Course.
 *
 * Domain hanya bergantung pada INTERFACE ini (Dependency Inversion).
 * Implementasi konkret (Eloquent) berada di layer Infrastructure sehingga
 * domain tetap bersih dari detail persistensi.
 */
interface CourseRepository
{
    /**
     * Menghasilkan identitas baru untuk Course.
     */
    public function nextIdentity(): CourseId;

    /**
     * Menyimpan (create/update) sebuah Course.
     */
    public function save(Course $course): void;

    /**
     * Mencari Course berdasarkan id; null bila tidak ada.
     */
    public function findById(CourseId $id): ?Course;

    /**
     * Mengambil seluruh Course (untuk panel admin).
     *
     * @return Course[]
     */
    public function all(): array;

    /**
     * Mengambil hanya Course yang published (untuk katalog mahasiswa).
     *
     * @return Course[]
     */
    public function published(): array;
}
