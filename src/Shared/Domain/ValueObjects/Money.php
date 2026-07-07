<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Money Value Object (Shared Kernel).
 *
 * Merepresentasikan nilai uang secara immutable dalam satuan terkecil
 * (untuk IDR kita gunakan "sen"/integer agar bebas dari galat floating point).
 *
 * Value Object dibandingkan berdasarkan NILAI, bukan identitas — dua Money
 * dengan amount & currency sama dianggap sama.
 */
final class Money
{
    /**
     * @param  int  $amount  Jumlah dalam satuan terkecil (mis. rupiah penuh untuk IDR).
     * @param  string  $currency  Kode mata uang ISO-4217 (mis. "IDR").
     */
    private function __construct(
        public readonly int $amount,
        public readonly string $currency,
    ) {
        // Invariant: uang tidak boleh negatif dalam domain ini.
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount tidak boleh negatif.');
        }

        // Invariant: currency wajib 3 huruf (ISO-4217).
        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('Currency harus 3 huruf ISO-4217.');
        }
    }

    /**
     * Named constructor untuk membuat Money dalam rupiah.
     */
    public static function idr(int $amount): self
    {
        return new self($amount, 'IDR');
    }

    /**
     * Named constructor generik.
     */
    public static function of(int $amount, string $currency): self
    {
        return new self($amount, strtoupper($currency));
    }

    /**
     * Menjumlahkan dua Money. Menghasilkan instance BARU (immutable).
     */
    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Mengalikan nominal dengan kuantitas (mis. harga x jumlah item).
     */
    public function multipliedBy(int $multiplier): self
    {
        if ($multiplier < 0) {
            throw new InvalidArgumentException('Multiplier tidak boleh negatif.');
        }

        return new self($this->amount * $multiplier, $this->currency);
    }

    /**
     * Perbandingan berbasis nilai (ciri khas Value Object).
     */
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }

    /**
     * Apakah nominalnya nol (dipakai untuk cek gratis, dsb.).
     */
    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    /**
     * Representasi manusiawi, mis. "Rp 150.000".
     */
    public function format(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }

    /**
     * Guard: operasi aritmetika hanya valid untuk currency yang sama.
     */
    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Tidak bisa mengoperasikan currency berbeda.');
        }
    }
}
