<?php

declare(strict_types=1);

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Src\Catalog\Domain\Repository\CourseRepository;
use Src\Catalog\Infrastructure\Persistence\EloquentCourseRepository;
use Src\Enrollment\Domain\Repository\EnrollmentRepository;
use Src\Enrollment\Infrastructure\Persistence\EloquentEnrollmentRepository;
use Src\Ordering\Domain\Repository\OrderRepository;
use Src\Ordering\Infrastructure\Persistence\EloquentOrderRepository;
use Src\Payment\Domain\PaymentGateway;
use Src\Payment\Infrastructure\Doku\DokuHttpGateway;
use Src\Payment\Infrastructure\Fake\FakeDokuGateway;

/**
 * DomainServiceProvider — pusat Dependency Injection untuk seluruh context.
 *
 * Di sinilah Dependency Inversion "ditutup": setiap INTERFACE domain
 * (repository & port gateway) dipetakan ke IMPLEMENTASI konkret di layer
 * Infrastructure. Domain & Application tetap tidak tahu detail ini.
 */
class DomainServiceProvider extends ServiceProvider
{
    /**
     * Binding kontrak -> implementasi.
     */
    public function register(): void
    {
        // --- Repository Pattern: interface domain -> implementasi Eloquent ---
        $this->app->bind(CourseRepository::class, EloquentCourseRepository::class);
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(EnrollmentRepository::class, EloquentEnrollmentRepository::class);

        // --- Anti-Corruption Layer: pilih adapter gateway sesuai konfigurasi ---
        $this->app->singleton(PaymentGateway::class, function ($app): PaymentGateway {
            $driver = config('payment.driver', 'fake');

            // Adapter nyata DOKU (aktif bila PAYMENT_DRIVER=doku + kredensial ada).
            if ($driver === 'doku') {
                return new DokuHttpGateway(
                    http: new Client(['timeout' => 15]),
                    baseUrl: (string) config('payment.doku.base_url'),
                    clientId: (string) config('payment.doku.client_id'),
                    secretKey: (string) config('payment.doku.secret_key'),
                );
            }

            // Default: adapter simulasi, jalan tanpa kredensial (demo & test).
            return new FakeDokuGateway(
                pollsUntilPaid: (int) config('payment.fake.polls_until_paid', 1),
            );
        });
    }
}
