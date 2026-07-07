<?php

declare(strict_types=1);

namespace Src\Billing\Application\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Billing\Application\BillingService;

/**
 * PollInvoicePaymentJob — job antrean untuk "polling ke DOKU".
 *
 * Mewakili langkah "Check Pelunasan -> polling ke DOKU" pada alur bisnis.
 * Job ini bisa didispatch berkala (mis. dari scheduler) atau manual saat
 * mahasiswa menekan tombol "Cek Status Pembayaran".
 */
final class PollInvoicePaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $invoiceId,
    ) {}

    /**
     * Mendelegasikan ke BillingService yang memanggil ACL gateway.
     */
    public function handle(BillingService $billing): void
    {
        $billing->pollPaymentStatus($this->invoiceId);
    }
}
