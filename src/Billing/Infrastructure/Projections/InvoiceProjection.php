<?php

declare(strict_types=1);

namespace Src\Billing\Infrastructure\Projections;

use Illuminate\Database\Eloquent\Model;

/**
 * InvoiceProjection — READ MODEL Eloquent untuk Billing (CQRS query-side).
 *
 * Baris di tabel `invoices` diisi & dimutakhirkan HANYA oleh InvoiceProjector
 * sebagai reaksi atas event. UI/Controller membaca dari sini (query), tidak
 * pernah menulis langsung — inilah pemisahan Command vs Query (CQRS).
 *
 * @property string $id
 * @property string $invoice_number
 * @property string $order_id
 * @property int $student_id
 * @property string $course_id
 * @property string $course_title
 * @property int $amount
 * @property string $status
 * @property string|null $gateway_reference
 * @property string|null $checkout_url
 * @property int $poll_attempts
 */
class InvoiceProjection extends Model
{
    protected $table = 'invoices';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'integer',
        'poll_attempts' => 'integer',
        'paid_at' => 'datetime',
    ];

    /**
     * Helper baca: apakah invoice sudah lunas.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
