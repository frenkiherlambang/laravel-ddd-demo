<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use Src\Ordering\Domain\Model\Order;
use Src\Ordering\Domain\Model\OrderId;
use Src\Ordering\Domain\Model\OrderStatus;
use Src\Shared\Domain\ValueObjects\Money;

/*
| Test DOMAIN CLASS: Order (Aggregate Root, Ordering context).
| Murni tanpa DB — menguji invariant transisi status.
*/

/**
 * Helper membuat order Pending untuk pengujian.
 */
function makeOrder(): Order
{
    return Order::place(
        OrderId::generate(),
        studentId: '1',
        courseId: (string) Uuid::uuid4(),
        courseTitle: 'Kursus Uji',
        amount: Money::idr(100000),
    );
}

it('order baru berstatus pending', function () {
    expect(makeOrder()->status())->toBe(OrderStatus::Pending);
});

it('menyimpan snapshot judul dan harga saat order dibuat', function () {
    $order = makeOrder();

    expect($order->courseTitle())->toBe('Kursus Uji')
        ->and($order->amount()->amount)->toBe(100000);
});

it('checkout mengubah status menjadi checked out', function () {
    $order = makeOrder();
    $order->checkout();

    expect($order->status())->toBe(OrderStatus::CheckedOut);
});

it('menolak checkout dua kali', function () {
    $order = makeOrder();
    $order->checkout();
    $order->checkout(); // tidak lagi pending -> harus gagal
})->throws(DomainException::class);

it('markPaid hanya valid setelah checkout', function () {
    $order = makeOrder();
    $order->checkout();
    $order->markPaid();

    expect($order->status())->toBe(OrderStatus::Paid);
});

it('menolak markPaid bila belum checkout', function () {
    makeOrder()->markPaid();
})->throws(DomainException::class);

it('menolak pembatalan order yang sudah lunas', function () {
    $order = makeOrder();
    $order->checkout();
    $order->markPaid();
    $order->cancel(); // sudah paid -> harus gagal
})->throws(DomainException::class);

it('mengizinkan pembatalan order yang masih pending', function () {
    $order = makeOrder();
    $order->cancel();

    expect($order->status())->toBe(OrderStatus::Cancelled);
});
