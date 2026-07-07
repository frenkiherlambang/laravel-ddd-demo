<?php

declare(strict_types=1);

namespace Src\Payment\Domain;

/**
 * PaymentGateway — Port Anti-Corruption Layer ke Payment Gateway Context.
 *
 * Domain Billing HANYA bergantung pada interface netral ini. Ia tidak tahu
 * apakah di baliknya ada DOKU, midtrans, atau simulasi. Semua istilah &
 * struktur data spesifik vendor "dicegat" dan diterjemahkan oleh adapter
 * konkret di layer Infrastructure (DokuHttpGateway / FakeDokuGateway).
 *
 * Inilah wujud Anti-Corruption Layer: model eksternal tidak boleh "bocor"
 * dan mencemari model domain kita.
 */
interface PaymentGateway
{
    /**
     * Membuat sesi pembayaran (checkout) dan mengembalikan URL + referensi.
     */
    public function createPayment(CreatePaymentRequest $request): PaymentSession;

    /**
     * Polling status pembayaran berdasarkan referensi gateway.
     *
     * Mengembalikan PaymentStatus netral (bukan kode mentah vendor).
     */
    public function fetchStatus(string $gatewayReference): PaymentStatus;
}
