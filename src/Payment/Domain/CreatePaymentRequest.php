<?php

declare(strict_types=1);

namespace Src\Payment\Domain;

use Src\Shared\Domain\ValueObjects\Money;

/**
 * CreatePaymentRequest — DTO permintaan pembuatan transaksi pembayaran.
 *
 * DTO netral yang dikirim domain Billing ke gateway MELALUI interface.
 * Tidak memuat istilah/struktur khas DOKU — itulah inti Anti-Corruption Layer:
 * domain berbicara dalam bahasanya sendiri, adapter yang menerjemahkan.
 */
final readonly class CreatePaymentRequest
{
    public function __construct(
        /** Nomor invoice internal kita (dipakai sebagai referensi order/invoice). */
        public string $invoiceNumber,
        /** Nominal yang harus dibayar. */
        public Money $amount,
        /** Nama pelanggan (mahasiswa). */
        public string $customerName,
        /** Email pelanggan. */
        public string $customerEmail,
        /** Deskripsi ringkas (mis. judul kursus). */
        public string $description,
    ) {}
}
