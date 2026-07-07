<?php

declare(strict_types=1);

namespace Src\Enrollment\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * EnrollmentEloquentModel — model persistensi untuk tabel `enrollments`.
 *
 * @property string $id
 * @property int $student_id
 * @property string $course_id
 */
class EnrollmentEloquentModel extends Model
{
    use HasUuids;

    protected $table = 'enrollments';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];
}
