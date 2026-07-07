<?php

declare(strict_types=1);

namespace Src\Billing\Domain\ValueObjects;

/**
 * InvoiceStatus — status invoice dalam domain Billing.
 */
enum InvoiceStatus: string
{
    /** Invoice dibuat, menunggu pembayaran. */
    case Pending = 'pending';

    /** Invoice lunas. */
    case Paid = 'paid';
}
