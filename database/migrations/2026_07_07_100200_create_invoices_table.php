<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel READ MODEL untuk Billing (CQRS query-side).
 *
 * Tabel ini TIDAK ditulis langsung oleh domain. Ia dibangun sepenuhnya oleh
 * InvoiceProjector dari stream event (stored_events). Boleh dihapus & dibangun
 * ulang kapan saja lewat `php artisan event-sourcing:replay`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            // uuid = id aggregate (InvoiceAggregateRoot).
            $table->uuid('id')->primary();
            $table->string('invoice_number')->unique();
            $table->uuid('order_id');
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('course_id');
            $table->string('course_title');
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('IDR');
            $table->string('status')->default('pending');
            // Data hasil integrasi gateway (via ACL).
            $table->string('gateway_reference')->nullable();
            $table->text('checkout_url')->nullable();
            // Audit ringkas: berapa kali pelunasan dicek.
            $table->unsignedInteger('poll_attempts')->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
