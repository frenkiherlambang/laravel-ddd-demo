<?php

declare(strict_types=1);

namespace Src\Payment\Domain;

/**
 * PaymentStatus — status pembayaran dalam BAHASA DOMAIN kita sendiri.
 *
 * Ini bagian dari Anti-Corruption Layer: apa pun istilah/kode yang dipakai
 * DOKU (mis. "SUCCESS", "SETTLEMENT", "EXPIRED"), semuanya diterjemahkan
 * menjadi enum netral ini sebelum masuk ke domain Billing.
 */
enum PaymentStatus: string
{
    /** Menunggu pembayaran. */
    case Pending = 'pending';

    /** Sudah lunas/terbayar. */
    case Paid = 'paid';

    /** Gagal atau kedaluwarsa. */
    case Failed = 'failed';
}
