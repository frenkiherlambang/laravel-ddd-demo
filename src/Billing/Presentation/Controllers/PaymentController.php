<?php

declare(strict_types=1);

namespace Src\Billing\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Src\Billing\Application\Jobs\PollInvoicePaymentJob;
use Src\Billing\Infrastructure\Projections\InvoiceProjection;

/**
 * PaymentController — Presentation (mahasiswa) untuk halaman pembayaran.
 *
 * Membaca dari READ MODEL (InvoiceProjection) — sisi Query pada CQRS.
 * Aksi menulis (polling) didelegasikan ke job/aggregate — sisi Command.
 */
final class PaymentController extends Controller
{
    /**
     * Menampilkan detail invoice + tombol menuju checkout gateway & cek status.
     */
    public function show(string $invoice): View
    {
        $projection = $this->ownedInvoiceOrFail($invoice);

        return view('payment.show', ['invoice' => $projection]);
    }

    /**
     * "Check Pelunasan": polling status ke gateway (DOKU) via job sinkron.
     *
     * Setelah polling, bila lunas, Reactor sudah memberi akses kursus.
     * Kita muat ulang read model untuk mengarahkan mahasiswa sesuai status.
     */
    public function poll(string $invoice): RedirectResponse
    {
        $this->ownedInvoiceOrFail($invoice);

        // COMMAND-side: jalankan polling (memanggil ACL + aggregate).
        PollInvoicePaymentJob::dispatchSync($invoice);

        // QUERY-side: baca ulang status terbaru.
        $projection = InvoiceProjection::query()->find($invoice);

        if ($projection?->isPaid()) {
            return redirect()
                ->route('payment.show', ['invoice' => $invoice])
                ->with('status', 'Pembayaran terkonfirmasi lunas. Akses kursus telah diberikan.');
        }

        return redirect()
            ->route('payment.show', ['invoice' => $invoice])
            ->with('status', 'Pembayaran masih pending. Silakan cek lagi setelah membayar.');
    }

    /**
     * Guard: ambil invoice milik mahasiswa saat ini, atau 403/404.
     */
    private function ownedInvoiceOrFail(string $invoice): InvoiceProjection
    {
        $projection = InvoiceProjection::query()->findOrFail($invoice);

        abort_if((int) $projection->student_id !== (int) auth()->id(), 403);

        return $projection;
    }
}
