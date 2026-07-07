<?php

declare(strict_types=1);

namespace Src\Payment\Infrastructure\Fake;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Src\Payment\Domain\CreatePaymentRequest;
use Src\Payment\Domain\PaymentGateway;
use Src\Payment\Domain\PaymentSession;
use Src\Payment\Domain\PaymentStatus;

/**
 * FakeDokuGateway — adapter ACL SIMULASI untuk Payment Gateway Context.
 *
 * Adapter default agar demo bisa berjalan tanpa kredensial DOKU asli.
 * Ia mensimulasikan perilaku DOKU:
 * - createPayment  -> menghasilkan reference + URL checkout palsu, status "pending".
 * - fetchStatus    -> polling; pada percobaan ke-N status berubah jadi "paid",
 *   meniru jeda settlement pada gateway nyata.
 *
 * State transaksi disimpan di cache agar polling lintas-request konsisten.
 * Adapter inilah yang juga dipakai dalam pengujian.
 */
final class FakeDokuGateway implements PaymentGateway
{
    /**
     * @param  int  $pollsUntilPaid  Berapa kali polling sebelum dianggap lunas.
     */
    public function __construct(
        private readonly int $pollsUntilPaid = 1,
    ) {}

    /**
     * Membuat "transaksi" simulasi dan menyimpan state awal (pending).
     */
    public function createPayment(CreatePaymentRequest $request): PaymentSession
    {
        // Reference bergaya DOKU untuk realisme demo.
        $reference = 'FAKE-DOKU-'.Str::upper(Str::random(12));

        // Simpan state simulasi: mulai dari 0 polling, belum paid.
        Cache::put($this->key($reference), [
            'invoice' => $request->invoiceNumber,
            'amount' => $request->amount->amount,
            'polls' => 0,
        ], now()->addHours(2));

        // URL checkout simulasi (route lokal yang menampilkan halaman "DOKU").
        return new PaymentSession(
            gatewayReference: $reference,
            checkoutUrl: route('payment.fake-checkout', ['reference' => $reference]),
        );
    }

    /**
     * Polling status. Menaikkan counter; setelah ambang tercapai -> Paid.
     *
     * Perhatikan: keluarannya adalah PaymentStatus NETRAL, bukan kode DOKU.
     */
    public function fetchStatus(string $gatewayReference): PaymentStatus
    {
        $state = Cache::get($this->key($gatewayReference));

        // Referensi tak dikenal dianggap gagal.
        if ($state === null) {
            return PaymentStatus::Failed;
        }

        $state['polls']++;
        Cache::put($this->key($gatewayReference), $state, now()->addHours(2));

        // Terjemahan "aturan settlement" simulasi menjadi status domain.
        return $state['polls'] >= $this->pollsUntilPaid
            ? PaymentStatus::Paid
            : PaymentStatus::Pending;
    }

    /**
     * Memaksa transaksi menjadi lunas — dipanggil saat mahasiswa menekan
     * "Bayar Sekarang" di halaman checkout simulasi.
     */
    public function forcePaid(string $gatewayReference): void
    {
        $state = Cache::get($this->key($gatewayReference));

        if ($state !== null) {
            $state['polls'] = $this->pollsUntilPaid;
            Cache::put($this->key($gatewayReference), $state, now()->addHours(2));
        }
    }

    /**
     * Kunci cache untuk sebuah reference.
     */
    private function key(string $reference): string
    {
        return 'fake_doku:'.$reference;
    }
}
