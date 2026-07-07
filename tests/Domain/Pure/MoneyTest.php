<?php

declare(strict_types=1);

use Src\Shared\Domain\ValueObjects\Money;

/*
| Test DOMAIN CLASS: Money (Value Object, Shared Kernel).
| Murni tanpa Laravel/DB — menguji invariant & perilaku nilai.
*/

it('membuat money rupiah dengan benar', function () {
    $money = Money::idr(150000);

    expect($money->amount)->toBe(150000)
        ->and($money->currency)->toBe('IDR');
});

it('menolak nominal negatif', function () {
    Money::idr(-1);
})->throws(InvalidArgumentException::class);

it('menolak currency yang tidak 3 huruf', function () {
    Money::of(1000, 'RUPIAH');
})->throws(InvalidArgumentException::class);

it('menjumlahkan dua money dan menghasilkan instance baru (immutable)', function () {
    $a = Money::idr(100000);
    $b = Money::idr(50000);

    $sum = $a->add($b);

    expect($sum->amount)->toBe(150000)
        // Operand asli tidak berubah (immutability).
        ->and($a->amount)->toBe(100000)
        ->and($sum)->not->toBe($a);
});

it('menolak penjumlahan currency berbeda', function () {
    Money::idr(1000)->add(Money::of(1000, 'USD'));
})->throws(InvalidArgumentException::class);

it('mengalikan nominal dengan kuantitas', function () {
    expect(Money::idr(25000)->multipliedBy(3)->amount)->toBe(75000);
});

it('membandingkan money berdasarkan nilai, bukan identitas', function () {
    expect(Money::idr(1000)->equals(Money::idr(1000)))->toBeTrue()
        ->and(Money::idr(1000)->equals(Money::idr(2000)))->toBeFalse();
});

it('memformat ke rupiah yang mudah dibaca', function () {
    expect(Money::idr(1500000)->format())->toBe('Rp 1.500.000');
});

it('mengetahui nilai nol', function () {
    expect(Money::idr(0)->isZero())->toBeTrue()
        ->and(Money::idr(1)->isZero())->toBeFalse();
});
