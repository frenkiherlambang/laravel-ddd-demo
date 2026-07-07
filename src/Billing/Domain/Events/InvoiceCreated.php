<?php

declare(strict_types=1);

namespace Src\Billing\Domain\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

/**
 * InvoiceCreated — Domain Event (write-side / Event Sourcing).
 *
 * Direkam ketika invoice pertama kali dibuat dalam status "pending".
 * Event inilah fakta yang disimpan permanen di event store (stored_events),
 * bukan state akhirnya — sesuai prinsip Event Sourcing.
 */
final class InvoiceCreated extends ShouldBeStored
{
    public function __construct(
        public readonly string $invoiceId,
        public readonly string $invoiceNumber,
        public readonly string $orderId,
        public readonly int $studentId,
        public readonly string $courseId,
        public readonly string $courseTitle,
        public readonly int $amount,
        public readonly string $currency,
    ) {}
}
