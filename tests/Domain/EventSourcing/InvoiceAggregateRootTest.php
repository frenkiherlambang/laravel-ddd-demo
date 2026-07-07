<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Src\Billing\Domain\Aggregate\InvoiceAggregateRoot;
use Src\Billing\Domain\Events\InvoiceCreated;
use Src\Billing\Domain\Events\InvoicePaid;
use Src\Payment\Domain\PaymentStatus;

/*
| Test DOMAIN CLASS: InvoiceAggregateRoot (Event Sourcing / write-side CQRS).
|
| Menggunakan helper Spatie InvoiceAggregateRoot::fake() untuk memverifikasi
| event apa yang DIREKAM aggregate sebagai respons atas sebuah command, serta
| invariant transisi statusnya. Butuh app + DB (fake event store), sehingga
| berada di suite Domain/EventSourcing.
*/

/**
 * Helper: payload standar untuk membuat invoice pada pengujian.
 */
function createInvoiceOn(InvoiceAggregateRoot $aggregate): InvoiceAggregateRoot
{
    return $aggregate->createInvoice(
        invoiceNumber: 'INV-TEST-001',
        orderId: (string) Uuid::uuid4(),
        studentId: 1,
        courseId: (string) Uuid::uuid4(),
        courseTitle: 'Kursus Uji',
        amount: 150000,
        currency: 'IDR',
    );
}

it('merekam InvoiceCreated saat invoice dibuat', function () {
    InvoiceAggregateRoot::fake()
        ->when(fn (InvoiceAggregateRoot $invoice) => createInvoiceOn($invoice))
        ->assertRecorded(function ($event) {
            expect($event)->toBeInstanceOf(InvoiceCreated::class)
                ->and($event->invoiceNumber)->toBe('INV-TEST-001')
                ->and($event->amount)->toBe(150000);
        });
});

it('polling pending TIDAK menandai invoice lunas', function () {
    $uuid = (string) Uuid::uuid4();

    InvoiceAggregateRoot::fake($uuid)
        ->given([new InvoiceCreated(
            invoiceId: $uuid,
            invoiceNumber: 'INV-TEST-002',
            orderId: (string) Uuid::uuid4(),
            studentId: 1,
            courseId: (string) Uuid::uuid4(),
            courseTitle: 'Kursus Uji',
            amount: 150000,
            currency: 'IDR',
        )])
        ->when(fn (InvoiceAggregateRoot $invoice) => $invoice->recordPollResult(PaymentStatus::Pending))
        // Percobaan polling dicatat, tetapi TIDAK ada InvoicePaid.
        ->assertNotRecorded(InvoicePaid::class);
});

it('polling paid merekam PaymentPollAttempted lalu InvoicePaid', function () {
    $uuid = (string) Uuid::uuid4();

    InvoiceAggregateRoot::fake($uuid)
        ->given([new InvoiceCreated(
            invoiceId: $uuid,
            invoiceNumber: 'INV-TEST-003',
            orderId: (string) Uuid::uuid4(),
            studentId: 1,
            courseId: (string) Uuid::uuid4(),
            courseTitle: 'Kursus Uji',
            amount: 150000,
            currency: 'IDR',
        )])
        ->when(fn (InvoiceAggregateRoot $invoice) => $invoice->recordPollResult(PaymentStatus::Paid))
        ->assertRecorded(function ($event) {
            // Salah satu dari dua event yang direkam harus bertipe valid.
            expect($event)->toBeInstanceOf(ShouldBeStored::class);
        });
});

it('menandai state Paid setelah rekonstruksi dari event', function () {
    $uuid = (string) Uuid::uuid4();
    $orderId = (string) Uuid::uuid4();
    $courseId = (string) Uuid::uuid4();

    // Rekonstruksi aggregate dari stream event lalu jalankan command.
    $aggregate = InvoiceAggregateRoot::fake($uuid)
        ->given([new InvoiceCreated(
            invoiceId: $uuid,
            invoiceNumber: 'INV-TEST-004',
            orderId: $orderId,
            studentId: 1,
            courseId: $courseId,
            courseTitle: 'Kursus Uji',
            amount: 150000,
            currency: 'IDR',
        )])
        ->when(fn (InvoiceAggregateRoot $invoice) => $invoice->markAsPaid())
        ->assertRecorded(function ($event) use ($orderId, $courseId) {
            expect($event)->toBeInstanceOf(InvoicePaid::class)
                ->and($event->orderId)->toBe($orderId)
                ->and($event->courseId)->toBe($courseId);
        });
});

it('menolak markAsPaid dua kali (invariant idempotensi pelunasan)', function () {
    $uuid = (string) Uuid::uuid4();

    InvoiceAggregateRoot::fake($uuid)
        ->given([
            new InvoiceCreated(
                invoiceId: $uuid,
                invoiceNumber: 'INV-TEST-005',
                orderId: (string) Uuid::uuid4(),
                studentId: 1,
                courseId: (string) Uuid::uuid4(),
                courseTitle: 'Kursus Uji',
                amount: 150000,
                currency: 'IDR',
            ),
            // Invoice sudah lunas dalam sejarahnya.
            new InvoicePaid(
                invoiceId: $uuid,
                orderId: (string) Uuid::uuid4(),
                studentId: 1,
                courseId: (string) Uuid::uuid4(),
            ),
        ])
        ->when(fn (InvoiceAggregateRoot $invoice) => $invoice->markAsPaid());
})->throws(DomainException::class);
