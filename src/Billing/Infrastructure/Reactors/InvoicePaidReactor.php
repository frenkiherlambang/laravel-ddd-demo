<?php

declare(strict_types=1);

namespace Src\Billing\Infrastructure\Reactors;

use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use Src\Billing\Domain\Events\InvoicePaid;
use Src\Enrollment\Application\EnrollmentService;
use Src\Ordering\Application\OrderService;

/**
 * InvoicePaidReactor — Reactor (side-effects) untuk event InvoicePaid.
 *
 * Perbedaan Reactor vs Projector: Projector hanya membangun read model
 * (idempoten, aman di-replay). Reactor menjalankan EFEK SAMPING dunia nyata
 * yang TIDAK boleh terjadi saat replay (mis. kirim email, ubah state context
 * lain). Karena itu Spatie tidak memanggil Reactor ketika me-replay event.
 *
 * Di sini, ketika invoice lunas, kita:
 * 1. Menandai Order terkait sebagai lunas (Ordering context).
 * 2. Memberi mahasiswa akses ke kursus (Enrollment context).
 *
 * Reactor inilah "lem" antar bounded context yang menjaga mereka tetap
 * terpisah namun terkoordinasi lewat event.
 */
final class InvoicePaidReactor extends Reactor
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly EnrollmentService $enrollments,
    ) {}

    /**
     * Menangani event InvoicePaid.
     */
    public function onInvoicePaid(InvoicePaid $event): void
    {
        // (1) Ordering: tandai order lunas (transisi CheckedOut -> Paid).
        $this->orders->markPaid($event->orderId);

        // (2) Enrollment: mahasiswa mendapat akses ke kursus.
        $this->enrollments->grantAccess($event->studentId, $event->courseId);
    }
}
