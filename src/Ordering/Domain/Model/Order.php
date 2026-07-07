<?php

declare(strict_types=1);

namespace Src\Ordering\Domain\Model;

use DomainException;
use Src\Shared\Domain\ValueObjects\Money;

/**
 * Order — Aggregate Root pada Ordering bounded context.
 *
 * Merepresentasikan pilihan kursus seorang mahasiswa dan alur checkout-nya.
 * Untuk menjaga demo tetap fokus, satu Order mewakili satu kursus.
 * Seluruh aturan transisi status dijaga di sini sebagai invariant domain.
 */
final class Order
{
    /**
     * @param  OrderId  $id  Identitas order.
     * @param  string  $studentId  Id mahasiswa pemilik order (referensi ke context Auth).
     * @param  string  $courseId  Id kursus yang dipilih (referensi ke Catalog context).
     * @param  string  $courseTitle  Snapshot judul kursus saat order dibuat.
     * @param  Money  $amount  Snapshot harga saat order dibuat (harga terkunci).
     * @param  OrderStatus  $status  Status siklus hidup order.
     */
    private function __construct(
        private readonly OrderId $id,
        private readonly string $studentId,
        private readonly string $courseId,
        private readonly string $courseTitle,
        private readonly Money $amount,
        private OrderStatus $status,
    ) {}

    /**
     * Factory: mahasiswa memilih kursus (membuat Order status Pending).
     * Harga & judul di-"snapshot" agar order tak berubah bila katalog berubah.
     */
    public static function place(
        OrderId $id,
        string $studentId,
        string $courseId,
        string $courseTitle,
        Money $amount,
    ): self {
        return new self($id, $studentId, $courseId, $courseTitle, $amount, OrderStatus::Pending);
    }

    /**
     * Factory: rekonstruksi dari penyimpanan (dipakai repository).
     */
    public static function reconstitute(
        OrderId $id,
        string $studentId,
        string $courseId,
        string $courseTitle,
        Money $amount,
        OrderStatus $status,
    ): self {
        return new self($id, $studentId, $courseId, $courseTitle, $amount, $status);
    }

    /**
     * Transisi: mahasiswa melakukan checkout.
     *
     * Invariant: hanya order Pending yang boleh di-checkout. Ini mencegah
     * checkout ganda atau checkout atas order yang sudah dibayar/dibatalkan.
     */
    public function checkout(): void
    {
        if ($this->status !== OrderStatus::Pending) {
            throw new DomainException('Hanya order berstatus pending yang bisa di-checkout.');
        }

        $this->status = OrderStatus::CheckedOut;
    }

    /**
     * Transisi: menandai order lunas.
     *
     * Invariant: hanya order yang sudah di-checkout yang bisa jadi Paid.
     * Dipanggil ketika Billing mengonfirmasi invoice terbayar.
     */
    public function markPaid(): void
    {
        if ($this->status !== OrderStatus::CheckedOut) {
            throw new DomainException('Order harus di-checkout sebelum bisa ditandai lunas.');
        }

        $this->status = OrderStatus::Paid;
    }

    /**
     * Transisi: membatalkan order.
     *
     * Invariant: order yang sudah Paid tidak bisa dibatalkan.
     */
    public function cancel(): void
    {
        if ($this->status === OrderStatus::Paid) {
            throw new DomainException('Order yang sudah lunas tidak bisa dibatalkan.');
        }

        $this->status = OrderStatus::Cancelled;
    }

    public function id(): OrderId
    {
        return $this->id;
    }

    public function studentId(): string
    {
        return $this->studentId;
    }

    public function courseId(): string
    {
        return $this->courseId;
    }

    public function courseTitle(): string
    {
        return $this->courseTitle;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }
}
