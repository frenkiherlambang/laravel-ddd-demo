<?php

declare(strict_types=1);

namespace Src\Ordering\Domain\Repository;

use Src\Ordering\Domain\Model\Order;
use Src\Ordering\Domain\Model\OrderId;

/**
 * OrderRepository — kontrak Repository Pattern untuk aggregate Order.
 */
interface OrderRepository
{
    public function nextIdentity(): OrderId;

    public function save(Order $order): void;

    public function findById(OrderId $id): ?Order;

    /**
     * Seluruh order milik seorang mahasiswa (untuk halaman "Pesanan Saya").
     *
     * @return Order[]
     */
    public function forStudent(string $studentId): array;
}
