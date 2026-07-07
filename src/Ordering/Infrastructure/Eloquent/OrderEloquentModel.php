<?php

declare(strict_types=1);

namespace Src\Ordering\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * OrderEloquentModel — model persistensi untuk tabel `orders`.
 *
 * @property string $id
 * @property int $student_id
 * @property string $course_id
 * @property string $course_title
 * @property int $amount
 * @property string $currency
 * @property string $status
 */
class OrderEloquentModel extends Model
{
    use HasUuids;

    protected $table = 'orders';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'integer',
    ];
}
