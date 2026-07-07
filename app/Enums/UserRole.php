<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * UserRole — peran pengguna pada sistem otentikasi (Breeze).
 *
 * Sengaja diletakkan di namespace App (bukan Src) karena ini bagian dari
 * "identity/access" lintas-context, bukan milik satu bounded context bisnis.
 */
enum UserRole: string
{
    /** Admin: membuat & mengelola kursus. */
    case Admin = 'admin';

    /** Mahasiswa: melihat katalog, order, bayar, mengakses kursus. */
    case Student = 'student';
}
