<?php

declare(strict_types=1);

namespace Src\Billing\Application;

use Illuminate\Support\Str;
use Src\Billing\Domain\Aggregate\InvoiceAggregateRoot;
use Src\Billing\Infrastructure\Projections\InvoiceProjection;
use Src\Payment\Domain\CreatePaymentRequest;
use Src\Payment\Domain\PaymentGateway;
use Src\Shared\Domain\ValueObjects\Money;

/**
 * BillingService — Application Service untuk Billing context (write-side).
 *
 * Mengorkestrasi:
 * 1. Membuat invoice PENDING (via InvoiceAggregateRoot -> Event Sourcing).
 * 2. Memanggil Anti-Corruption Layer (PaymentGateway) untuk membuat sesi
 *    pembayaran, lalu merekam referensi & URL checkout ke aggregate.
 * 3. Melakukan polling status pelunasan ke gateway dan merekam hasilnya.
 *
 * Perhatikan pemisahan CQRS: penulisan selalu lewat aggregate; pembacaan
 * (mis. mengambil URL checkout) lewat read model InvoiceProjection.
 */
final readonly class BillingService
{
    public function __construct(
        private PaymentGateway $gateway,
    ) {}

    /**
     * Use case: buat Invoice Pending untuk sebuah order, lalu mulai sesi
     * pembayaran di gateway (Arahkan ke Payment Gateway DOKU Checkout).
     *
     * @return string Id invoice (uuid aggregate) yang baru dibuat.
     */
    public function createPendingInvoiceForOrder(
        string $orderId,
        int $studentId,
        string $courseId,
        string $courseTitle,
        int $amount,
        string $studentName,
        string $studentEmail,
    ): string {
        // Identitas aggregate baru.
        $invoiceId = (string) Str::uuid();
        $invoiceNumber = 'INV-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));

        // (1) COMMAND: buat invoice pending (event InvoiceCreated direkam).
        $aggregate = InvoiceAggregateRoot::retrieve($invoiceId)
            ->createInvoice(
                invoiceNumber: $invoiceNumber,
                orderId: $orderId,
                studentId: $studentId,
                courseId: $courseId,
                courseTitle: $courseTitle,
                amount: $amount,
                currency: 'IDR',
            );

        // (2) ACL: minta gateway membuat sesi pembayaran (checkout DOKU).
        $session = $this->gateway->createPayment(new CreatePaymentRequest(
            invoiceNumber: $invoiceNumber,
            amount: Money::idr($amount),
            customerName: $studentName,
            customerEmail: $studentEmail,
            description: $courseTitle,
        ));

        // Rekam referensi & URL checkout ke aggregate, lalu persist semua event.
        $aggregate
            ->startGatewaySession($session->gatewayReference, $session->checkoutUrl)
            ->persist();

        return $invoiceId;
    }

    /**
     * Query (read-side): peta orderId => invoiceId untuk sekumpulan order.
     *
     * Dipakai halaman "Pesanan Saya" untuk menautkan tiap order ke invoice-nya
     * tanpa membocorkan detail read model Billing ke context Ordering.
     *
     * @param  array<int, string>  $orderIds
     * @return array<string, string>
     */
    public function invoiceIdsByOrderIds(array $orderIds): array
    {
        if ($orderIds === []) {
            return [];
        }

        return InvoiceProjection::query()
            ->whereIn('order_id', $orderIds)
            ->pluck('id', 'order_id')
            ->all();
    }

    /**
     * Use case: "Check Pelunasan" — polling status ke DOKU via ACL, lalu
     * merekam hasilnya. Bila lunas, aggregate merekam InvoicePaid yang akan
     * memicu Reactor (tandai order lunas + beri akses kursus).
     */
    public function pollPaymentStatus(string $invoiceId): void
    {
        // Query read model untuk mendapatkan referensi gateway.
        $projection = InvoiceProjection::query()->find($invoiceId);

        if ($projection === null || $projection->gateway_reference === null) {
            return;
        }

        // ACL: ambil status NETRAL dari gateway (bukan kode mentah DOKU).
        $status = $this->gateway->fetchStatus($projection->gateway_reference);

        // COMMAND: rekam hasil polling (dan tandai Paid bila perlu), persist.
        InvoiceAggregateRoot::retrieve($invoiceId)
            ->recordPollResult($status)
            ->persist();
    }
}
