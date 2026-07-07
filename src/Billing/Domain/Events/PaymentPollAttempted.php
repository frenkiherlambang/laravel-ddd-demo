<?php

declare(strict_types=1);

namespace Src\Billing\Domain\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

/**
 * PaymentPollAttempted — Domain Event (write-side / Event Sourcing).
 *
 * Direkam setiap kali sistem melakukan polling status ke Payment Gateway.
 * Menyimpan jejak percobaan ini memberi kita audit trail lengkap: berapa kali
 * dan kapan saja pelunasan dicek — salah satu keunggulan Event Sourcing.
 */
final class PaymentPollAttempted extends ShouldBeStored
{
    public function __construct(
        public readonly string $invoiceId,
        /** Status hasil polling dalam bahasa domain (pending/paid/failed). */
        public readonly string $observedStatus,
    ) {}
}
