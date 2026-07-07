<?php

declare(strict_types=1);

namespace Src\Ordering\Domain\Model;

/**
 * OrderStatus — status siklus hidup Order (Ordering context).
 *
 * Enum menjaga status tetap valid dan mempermudah transisi eksplisit.
 */
enum OrderStatus: string
{
    /** Order dibuat mahasiswa namun belum di-checkout (masih "keranjang"). */
    case Pending = 'pending';

    /** Order sudah di-checkout; invoice akan/telah dibuat di Billing. */
    case CheckedOut = 'checked_out';

    /** Pembayaran selesai (dikonfirmasi lewat integrasi Billing). */
    case Paid = 'paid';

    /** Order dibatalkan. */
    case Cancelled = 'cancelled';
}
