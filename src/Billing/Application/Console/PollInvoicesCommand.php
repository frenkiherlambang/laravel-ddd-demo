<?php

declare(strict_types=1);

namespace Src\Billing\Application\Console;

use Illuminate\Console\Command;
use Src\Billing\Application\Jobs\PollInvoicePaymentJob;
use Src\Billing\Infrastructure\Projections\InvoiceProjection;

/**
 * PollInvoicesCommand — perintah artisan untuk polling pelunasan.
 *
 * Penggunaan:
 *   php artisan invoice:poll {invoiceId?}   -> polling satu invoice
 *   php artisan invoice:poll                -> polling SEMUA invoice pending
 *
 * Mensimulasikan scheduler yang rutin "polling ke DOKU" untuk mengecek
 * pelunasan tanpa harus menunggu callback.
 */
final class PollInvoicesCommand extends Command
{
    protected $signature = 'invoice:poll {invoiceId? : Id invoice tertentu (opsional)}';

    protected $description = 'Polling status pelunasan invoice ke Payment Gateway (DOKU).';

    public function handle(): int
    {
        $invoiceId = $this->argument('invoiceId');

        // Kumpulkan target: satu invoice, atau seluruh invoice pending.
        $targets = $invoiceId
            ? [$invoiceId]
            : InvoiceProjection::query()->where('status', 'pending')->pluck('id')->all();

        if ($targets === []) {
            $this->info('Tidak ada invoice pending untuk di-polling.');

            return self::SUCCESS;
        }

        // Dispatch job polling untuk tiap invoice.
        foreach ($targets as $id) {
            PollInvoicePaymentJob::dispatchSync($id);
            $this->line("Polling invoice {$id} selesai.");
        }

        $this->info('Selesai polling '.count($targets).' invoice.');

        return self::SUCCESS;
    }
}
