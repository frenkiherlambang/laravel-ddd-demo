<?php

declare(strict_types=1);

namespace Src\Billing\Domain\Aggregate;

use DomainException;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Src\Billing\Domain\Events\InvoiceCreated;
use Src\Billing\Domain\Events\InvoicePaid;
use Src\Billing\Domain\Events\PaymentGatewaySessionStarted;
use Src\Billing\Domain\Events\PaymentPollAttempted;
use Src\Billing\Domain\ValueObjects\InvoiceStatus;
use Src\Payment\Domain\PaymentStatus;

/**
 * InvoiceAggregateRoot — Aggregate Root ber-Event-Sourcing (Billing context).
 *
 * Ini adalah WRITE MODEL dalam pola CQRS. Perbedaan mendasar dengan aggregate
 * biasa: state tidak disimpan langsung, melainkan direkonstruksi dengan
 * MEMUTAR ULANG (replay) daftar event dari event store.
 *
 * Pola pada tiap perintah (command method):
 * 1. Cek invariant menggunakan state saat ini.
 * 2. Panggil recordThat(new SomeEvent(...)) untuk MEREKAM fakta.
 * 3. Spatie memanggil applyXxx() untuk memutakhirkan state in-memory.
 *
 * State di sini hanya secukupnya untuk menjaga invariant; representasi kaya
 * untuk dibaca UI dibangun terpisah oleh Projector (read model).
 */
final class InvoiceAggregateRoot extends AggregateRoot
{
    /** Status invoice saat ini (hasil replay event). */
    private InvoiceStatus $status = InvoiceStatus::Pending;

    /** Jumlah percobaan polling — berguna untuk audit & invariant. */
    private int $pollAttempts = 0;

    /** Referensi lintas-context yang dibutuhkan saat menandai lunas. */
    private string $orderId = '';

    private int $studentId = 0;

    private string $courseId = '';

    /**
     * Command: membuat invoice pending.
     *
     * Invariant: sebuah aggregate hanya boleh dibuat sekali. Bila sudah ada
     * order (state terisi via replay), penciptaan ulang ditolak.
     */
    public function createInvoice(
        string $invoiceNumber,
        string $orderId,
        int $studentId,
        string $courseId,
        string $courseTitle,
        int $amount,
        string $currency,
    ): self {
        if ($this->orderId !== '') {
            throw new DomainException('Invoice sudah pernah dibuat untuk aggregate ini.');
        }

        $this->recordThat(new InvoiceCreated(
            invoiceId: $this->uuid(),
            invoiceNumber: $invoiceNumber,
            orderId: $orderId,
            studentId: $studentId,
            courseId: $courseId,
            courseTitle: $courseTitle,
            amount: $amount,
            currency: $currency,
        ));

        return $this;
    }

    /**
     * Command: mencatat bahwa sesi pembayaran gateway telah dibuat.
     */
    public function startGatewaySession(string $gatewayReference, string $checkoutUrl): self
    {
        $this->recordThat(new PaymentGatewaySessionStarted(
            invoiceId: $this->uuid(),
            gatewayReference: $gatewayReference,
            checkoutUrl: $checkoutUrl,
        ));

        return $this;
    }

    /**
     * Command: mencatat hasil polling status dan, bila lunas, menandai Paid.
     *
     * Menerima PaymentStatus NETRAL dari Anti-Corruption Layer (bukan kode DOKU).
     * Idempoten: bila sudah Paid, polling berikutnya tidak mengubah apa pun.
     */
    public function recordPollResult(PaymentStatus $status): self
    {
        // Selalu rekam percobaan polling untuk audit trail.
        $this->recordThat(new PaymentPollAttempted(
            invoiceId: $this->uuid(),
            observedStatus: $status->value,
        ));

        // Bila gateway melaporkan lunas dan invoice belum Paid -> tandai Paid.
        if ($status === PaymentStatus::Paid && $this->status !== InvoiceStatus::Paid) {
            $this->markAsPaid();
        }

        return $this;
    }

    /**
     * Command: menandai invoice lunas.
     *
     * Invariant: tidak boleh menandai lunas dua kali (mencegah double-enroll
     * dan efek hilir ganda).
     */
    public function markAsPaid(): self
    {
        if ($this->status === InvoiceStatus::Paid) {
            throw new DomainException('Invoice sudah lunas; tidak bisa ditandai lunas dua kali.');
        }

        $this->recordThat(new InvoicePaid(
            invoiceId: $this->uuid(),
            orderId: $this->orderId,
            studentId: $this->studentId,
            courseId: $this->courseId,
        ));

        return $this;
    }

    /**
     * Apply: memutakhirkan state saat event InvoiceCreated diputar ulang.
     */
    protected function applyInvoiceCreated(InvoiceCreated $event): void
    {
        $this->status = InvoiceStatus::Pending;
        $this->orderId = $event->orderId;
        $this->studentId = $event->studentId;
        $this->courseId = $event->courseId;
    }

    /**
     * Apply: menghitung percobaan polling.
     */
    protected function applyPaymentPollAttempted(PaymentPollAttempted $event): void
    {
        $this->pollAttempts++;
    }

    /**
     * Apply: menandai state lunas.
     */
    protected function applyInvoicePaid(InvoicePaid $event): void
    {
        $this->status = InvoiceStatus::Paid;
    }

    /**
     * Expose status (dipakai pengujian domain untuk verifikasi transisi).
     */
    public function status(): InvoiceStatus
    {
        return $this->status;
    }

    /**
     * Expose jumlah polling (dipakai pengujian domain).
     */
    public function pollAttempts(): int
    {
        return $this->pollAttempts;
    }
}
