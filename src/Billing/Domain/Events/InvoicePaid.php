<?php

declare(strict_types=1);

namespace Src\Billing\Domain\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

/**
 * InvoicePaid — Domain Event (write-side / Event Sourcing).
 *
 * Direkam ketika invoice dinyatakan lunas. Event inilah pemicu (lewat Reactor)
 * proses hilir: menandai Order lunas dan memberi mahasiswa akses ke kursus
 * (Enrollment). Ini contoh nyata bagaimana efek samping dipisah dari
 * perubahan state melalui Event Sourcing.
 */
final class InvoicePaid extends ShouldBeStored
{
    public function __construct(
        public readonly string $invoiceId,
        public readonly string $orderId,
        public readonly int $studentId,
        public readonly string $courseId,
    ) {}
}
