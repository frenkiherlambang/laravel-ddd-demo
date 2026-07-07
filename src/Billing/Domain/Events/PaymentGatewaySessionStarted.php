<?php

declare(strict_types=1);

namespace Src\Billing\Domain\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

/**
 * PaymentGatewaySessionStarted — Domain Event (write-side / Event Sourcing).
 *
 * Direkam ketika sesi pembayaran di gateway (DOKU) berhasil dibuat, sehingga
 * referensi gateway & URL checkout menjadi bagian dari riwayat invoice.
 */
final class PaymentGatewaySessionStarted extends ShouldBeStored
{
    public function __construct(
        public readonly string $invoiceId,
        public readonly string $gatewayReference,
        public readonly string $checkoutUrl,
    ) {}
}
