<?php

declare(strict_types=1);

namespace Src\Billing\Infrastructure\Projections;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Src\Billing\Domain\Events\InvoiceCreated;
use Src\Billing\Domain\Events\InvoicePaid;
use Src\Billing\Domain\Events\PaymentGatewaySessionStarted;
use Src\Billing\Domain\Events\PaymentPollAttempted;

/**
 * InvoiceProjector — membangun READ MODEL `invoices` dari stream event.
 *
 * Projector adalah komponen QUERY-side pada CQRS: ia MENDENGARKAN event yang
 * direkam aggregate lalu menuliskan/memutakhirkan proyeksi baca. Karena murni
 * turunan dari event, read model bisa dihapus & di-replay ulang kapan pun.
 *
 * Penamaan method mengikuti konvensi Spatie: on<NamaEvent>.
 */
final class InvoiceProjector extends Projector
{
    /**
     * Membuat baris read model saat invoice dibuat (status pending).
     */
    public function onInvoiceCreated(InvoiceCreated $event): void
    {
        InvoiceProjection::create([
            'id' => $event->invoiceId,
            'invoice_number' => $event->invoiceNumber,
            'order_id' => $event->orderId,
            'student_id' => $event->studentId,
            'course_id' => $event->courseId,
            'course_title' => $event->courseTitle,
            'amount' => $event->amount,
            'currency' => $event->currency,
            'status' => 'pending',
            'poll_attempts' => 0,
        ]);
    }

    /**
     * Menyimpan referensi & URL checkout dari gateway (via ACL).
     */
    public function onPaymentGatewaySessionStarted(PaymentGatewaySessionStarted $event): void
    {
        InvoiceProjection::where('id', $event->invoiceId)->update([
            'gateway_reference' => $event->gatewayReference,
            'checkout_url' => $event->checkoutUrl,
        ]);
    }

    /**
     * Menaikkan counter polling di read model (audit sederhana untuk UI).
     */
    public function onPaymentPollAttempted(PaymentPollAttempted $event): void
    {
        InvoiceProjection::where('id', $event->invoiceId)->increment('poll_attempts');
    }

    /**
     * Menandai read model lunas.
     */
    public function onInvoicePaid(InvoicePaid $event): void
    {
        InvoiceProjection::where('id', $event->invoiceId)->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
