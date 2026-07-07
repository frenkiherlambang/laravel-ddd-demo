<?php

declare(strict_types=1);

namespace Src\Payment\Infrastructure\Doku;

use GuzzleHttp\ClientInterface;
use RuntimeException;
use Src\Payment\Domain\CreatePaymentRequest;
use Src\Payment\Domain\PaymentGateway;
use Src\Payment\Domain\PaymentSession;
use Src\Payment\Domain\PaymentStatus;

/**
 * DokuHttpGateway — adapter ACL NYATA untuk DOKU Checkout (Jokul API).
 *
 * Inilah "penerjemah" Anti-Corruption Layer sesungguhnya. Tanggung jawabnya:
 * 1. Memetakan DTO domain (CreatePaymentRequest) -> payload JSON khas DOKU.
 * 2. Menandatangani request sesuai skema signature DOKU (HMAC-SHA256).
 * 3. Memetakan respons & kode status DOKU -> DTO/enum domain netral.
 *
 * Dengan begitu istilah DOKU ("SUCCESS", "invoice_number", "payment.url", dst.)
 * tidak pernah bocor ke domain Billing.
 *
 * Catatan: adapter ini aktif hanya bila kredensial DOKU diisi di konfigurasi.
 * Untuk demo default, binding memakai FakeDokuGateway.
 */
final class DokuHttpGateway implements PaymentGateway
{
    public function __construct(
        private readonly ClientInterface $http,
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $secretKey,
    ) {}

    /**
     * Membuat transaksi lewat endpoint DOKU Checkout.
     */
    public function createPayment(CreatePaymentRequest $request): PaymentSession
    {
        $requestId = uniqid('req-', true);
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $target = '/checkout/v1/payment';

        // (1) Terjemahan DTO domain -> struktur payload DOKU.
        $body = [
            'order' => [
                'amount' => $request->amount->amount,
                'invoice_number' => $request->invoiceNumber,
                'currency' => $request->amount->currency,
            ],
            'payment' => [
                'payment_due_date' => 60, // menit
            ],
            'customer' => [
                'name' => $request->customerName,
                'email' => $request->customerEmail,
            ],
        ];

        $rawBody = json_encode($body, JSON_THROW_ON_ERROR);

        // (2) Bangun signature sesuai spesifikasi DOKU.
        $signature = $this->signature($requestId, $timestamp, $target, $rawBody);

        $response = $this->http->request('POST', $this->baseUrl.$target, [
            'headers' => [
                'Client-Id' => $this->clientId,
                'Request-Id' => $requestId,
                'Request-Timestamp' => $timestamp,
                'Signature' => $signature,
                'Content-Type' => 'application/json',
            ],
            'body' => $rawBody,
        ]);

        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        // (3) Terjemahan respons DOKU -> DTO domain netral.
        $reference = $payload['response']['order']['invoice_number'] ?? $request->invoiceNumber;
        $url = $payload['response']['payment']['url'] ?? null;

        if ($url === null) {
            throw new RuntimeException('DOKU tidak mengembalikan URL pembayaran.');
        }

        return new PaymentSession(
            gatewayReference: (string) $reference,
            checkoutUrl: (string) $url,
        );
    }

    /**
     * Polling status transaksi ke DOKU lalu terjemahkan ke enum domain.
     */
    public function fetchStatus(string $gatewayReference): PaymentStatus
    {
        $requestId = uniqid('req-', true);
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $target = '/orders/v1/status/'.$gatewayReference;

        $signature = $this->signature($requestId, $timestamp, $target, '');

        $response = $this->http->request('GET', $this->baseUrl.$target, [
            'headers' => [
                'Client-Id' => $this->clientId,
                'Request-Id' => $requestId,
                'Request-Timestamp' => $timestamp,
                'Signature' => $signature,
            ],
        ]);

        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        // Kode status mentah khas DOKU (mis. SUCCESS / PENDING / EXPIRED / FAILED).
        $dokuStatus = strtoupper((string) ($payload['transaction']['status'] ?? 'PENDING'));

        return $this->mapStatus($dokuStatus);
    }

    /**
     * Inti Anti-Corruption Layer: memetakan kode DOKU -> PaymentStatus domain.
     */
    private function mapStatus(string $dokuStatus): PaymentStatus
    {
        return match ($dokuStatus) {
            'SUCCESS', 'SETTLEMENT', 'PAID' => PaymentStatus::Paid,
            'EXPIRED', 'FAILED', 'VOID' => PaymentStatus::Failed,
            default => PaymentStatus::Pending,
        };
    }

    /**
     * Membentuk header Signature sesuai skema DOKU (HMAC-SHA256, base64).
     */
    private function signature(string $requestId, string $timestamp, string $target, string $rawBody): string
    {
        // Digest body untuk komponen signature (kosong bila GET tanpa body).
        $digest = $rawBody === ''
            ? ''
            : 'Digest:'.base64_encode(hash('sha256', $rawBody, true))."\n";

        $componentSignature =
            "Client-Id:{$this->clientId}\n".
            "Request-Id:{$requestId}\n".
            "Request-Timestamp:{$timestamp}\n".
            "Request-Target:{$target}\n".
            $digest;

        $signature = base64_encode(
            hash_hmac('sha256', rtrim($componentSignature, "\n"), $this->secretKey, true)
        );

        return 'HMACSHA256='.$signature;
    }
}
