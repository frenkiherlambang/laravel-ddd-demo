<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * CourseEloquentModel — model persistensi (layer Infrastructure).
 *
 * Ini BUKAN objek domain. Perannya murni sebagai jembatan ke tabel `courses`.
 * Aggregate Course domain dipetakan ke/dari model ini oleh repository.
 *
 * @property string $id
 * @property string $title
 * @property string $description
 * @property int $price_amount
 * @property string $price_currency
 * @property bool $published
 */
class CourseEloquentModel extends Model
{
    use HasUuids;

    /**
     * Nama tabel eksplisit karena penamaan kelas tidak mengikuti konvensi.
     */
    protected $table = 'courses';

    /**
     * Primary key bertipe string (UUID), non-incrementing.
     */
    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Semua kolom mass-assignable karena akses hanya lewat repository.
     */
    protected $guarded = [];

    protected $casts = [
        'price_amount' => 'integer',
        'published' => 'boolean',
    ];
}
