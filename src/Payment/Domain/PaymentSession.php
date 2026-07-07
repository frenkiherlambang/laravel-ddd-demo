<?php

declare(strict_types=1);

namespace Src\Payment\Domain;

/**
 * PaymentSession — DTO hasil pembuatan transaksi di gateway.
 *
 * Menyembunyikan detail respons mentah DOKU. Domain hanya butuh:
 * - referensi transaksi di sisi gateway, dan
 * - URL checkout untuk mengarahkan mahasiswa.
 */
final readonly class PaymentSession
{
    public function __construct(
        /** Id transaksi di sisi gateway (mis. reference DOKU). */
        public string $gatewayReference,
        /** URL halaman pembayaran yang harus dibuka mahasiswa. */
        public string $checkoutUrl,
    ) {}
}
