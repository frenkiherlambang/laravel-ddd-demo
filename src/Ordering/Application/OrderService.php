<?php

declare(strict_types=1);

namespace Src\Ordering\Application;

use Src\Ordering\Domain\Model\Order;
use Src\Ordering\Domain\Model\OrderId;
use Src\Ordering\Domain\Repository\OrderRepository;
use Src\Shared\Domain\ValueObjects\Money;

/**
 * OrderService — Application Service (use case) untuk Ordering context.
 *
 * Menangani: mahasiswa memilih kursus (place order), checkout, serta
 * penandaan lunas (dipanggil dari Reactor Billing setelah InvoicePaid).
 */
final readonly class OrderService
{
    public function __construct(
        private OrderRepository $orders,
    ) {}

    /**
     * Use case: mahasiswa memilih kursus -> membuat Order pending.
     *
     * Menerima snapshot judul & harga dari Catalog agar order terkunci.
     */
    public function placeOrder(
        int $studentId,
        string $courseId,
        string $courseTitle,
        Money $price,
    ): OrderId {
        $order = Order::place(
            $this->orders->nextIdentity(),
            (string) $studentId,
            $courseId,
            $courseTitle,
            $price,
        );

        $this->orders->save($order);

        return $order->id();
    }

    /**
     * Use case: checkout sebuah order (transisi Pending -> CheckedOut).
     */
    public function checkout(string $orderId): Order
    {
        $order = $this->orders->findById(OrderId::fromString($orderId));

        if ($order === null) {
            throw new \RuntimeException('Order tidak ditemukan.');
        }

        $order->checkout();
        $this->orders->save($order);

        return $order;
    }

    /**
     * Dipanggil Reactor: menandai order lunas setelah invoice Paid.
     */
    public function markPaid(string $orderId): void
    {
        $order = $this->orders->findById(OrderId::fromString($orderId));

        if ($order === null) {
            return;
        }

        $order->markPaid();
        $this->orders->save($order);
    }

    public function find(string $orderId): ?Order
    {
        return $this->orders->findById(OrderId::fromString($orderId));
    }

    /**
     * @return Order[]
     */
    public function forStudent(int $studentId): array
    {
        return $this->orders->forStudent((string) $studentId);
    }
}
