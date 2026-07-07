<?php

declare(strict_types=1);

namespace Src\Payment\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Src\Billing\Application\Jobs\PollInvoicePaymentJob;
use Src\Billing\Infrastructure\Projections\InvoiceProjection;
use Src\Payment\Domain\PaymentGateway;
use Src\Payment\Infrastructure\Fake\FakeDokuGateway;

/**
 * FakeDokuCheckoutController — SIMULASI halaman "hosted checkout" DOKU.
 *
 * Dalam produksi, halaman ini milik DOKU. Di demo, kita meniru pengalaman:
 * mahasiswa membuka URL checkout, menekan "Bayar Sekarang", lalu transaksi
 * ditandai lunas di sisi (fake) gateway. Setelah itu kita polling agar
 * domain Billing memutakhirkan invoice menjadi Paid.
 */
final class FakeDokuCheckoutController extends Controller
{
    public function __construct(
        private readonly PaymentGateway $gateway,
    ) {}

    /**
     * Menampilkan halaman checkout simulasi berdasarkan reference gateway.
     */
    public function show(string $reference): View
    {
        // Cari invoice terkait via read model untuk menampilkan nominal.
        $invoice = InvoiceProjection::query()
            ->where('gateway_reference', $reference)
            ->firstOrFail();

        return view('payment.fake-doku', [
            'reference' => $reference,
            'invoice' => $invoice,
        ]);
    }

    /**
     * Aksi "Bayar Sekarang" pada halaman simulasi.
     *
     * Menandai transaksi lunas di fake gateway, lalu memicu polling sinkron
     * agar aggregate Billing merekam InvoicePaid (dan Reactor memberi akses).
     */
    public function pay(string $reference): RedirectResponse
    {
        $invoice = InvoiceProjection::query()
            ->where('gateway_reference', $reference)
            ->firstOrFail();

        // Hanya berlaku untuk adapter simulasi.
        if ($this->gateway instanceof FakeDokuGateway) {
            $this->gateway->forcePaid($reference);
        }

        // Polling agar domain memproses pelunasan.
        PollInvoicePaymentJob::dispatchSync($invoice->id);

        return redirect()
            ->route('payment.show', ['invoice' => $invoice->id])
            ->with('status', 'Pembayaran (simulasi DOKU) berhasil. Status invoice diperbarui.');
    }
}
