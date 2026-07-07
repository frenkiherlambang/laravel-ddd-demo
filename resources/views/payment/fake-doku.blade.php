{{-- SIMULASI halaman hosted checkout DOKU (di produksi ini milik DOKU) --}}
<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800">DOKU Checkout <span class="text-xs text-gray-400">(Simulasi)</span></h1>
        <p class="text-sm text-gray-500 mt-1">Halaman pembayaran pihak ketiga</p>
    </div>

    <div class="border rounded-lg p-4 mb-6 bg-gray-50">
        <div class="flex justify-between py-1">
            <span class="text-gray-600">Invoice</span>
            <span class="font-mono">{{ $invoice->invoice_number }}</span>
        </div>
        <div class="flex justify-between py-1">
            <span class="text-gray-600">Kursus</span>
            <span>{{ $invoice->course_title }}</span>
        </div>
        <div class="flex justify-between py-1 text-lg font-bold border-t mt-2 pt-2">
            <span>Total</span>
            <span>Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Menekan tombol ini mensimulasikan pembayaran berhasil di DOKU --}}
    <form method="POST" action="{{ route('payment.fake-pay', $reference) }}">
        @csrf
        <button type="submit"
                class="w-full px-4 py-3 bg-green-600 text-white font-semibold rounded hover:bg-green-500">
            Bayar Sekarang (Simulasi Sukses)
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('payment.show', $invoice->id) }}" class="text-sm text-gray-500 hover:underline">
            Batal &amp; kembali ke invoice
        </a>
    </div>
</x-guest-layout>
