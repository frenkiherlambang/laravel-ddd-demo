<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * Identifier Value Object berbasis UUID (Shared Kernel).
 *
 * Kelas dasar untuk semua ID entitas/aggregate di seluruh bounded context
 * (CourseId, OrderId, InvoiceId, dst). Dengan meng-extend kelas ini, tiap ID
 * menjadi bertipe kuat (type-safe) sehingga OrderId tidak bisa tertukar
 * dengan CourseId meski sama-sama string.
 */
abstract class Identifier
{
    /**
     * @param  string  $value  Representasi string dari UUID.
     */
    final public function __construct(public readonly string $value)
    {
        // Invariant: value harus UUID valid agar identitas selalu konsisten.
        if (! Uuid::isValid($value)) {
            throw new InvalidArgumentException(static::class.' harus UUID yang valid.');
        }
    }

    /**
     * Membuat identifier baru yang acak (dipakai saat membuat entitas baru).
     */
    public static function generate(): static
    {
        return new static(Uuid::uuid4()->toString());
    }

    /**
     * Merekonstruksi identifier dari string (mis. hasil query DB).
     */
    public static function fromString(string $value): static
    {
        return new static($value);
    }

    /**
     * Perbandingan berbasis nilai.
     */
    public function equals(self $other): bool
    {
        return $this::class === $other::class
            && $this->value === $other->value;
    }

    /**
     * Memudahkan penggunaan sebagai string (mis. di route / view).
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
