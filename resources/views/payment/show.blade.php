{{-- Halaman Invoice/Pembayaran (dibaca dari READ MODEL InvoiceProjection / CQRS) --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Pembayaran Invoice') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-blue-100 text-blue-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500">No. Invoice</p>
                        <p class="font-mono font-semibold text-gray-900">{{ $invoice->invoice_number }}</p>
                    </div>
                    @if ($invoice->isPaid())
                        <span class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded">LUNAS</span>
                    @else
                        <span class="px-3 py-1 text-sm bg-yellow-100 text-yellow-700 rounded">PENDING</span>
                    @endif
                </div>

                <div class="mt-6 space-y-2">
                    <div class="flex justify-between py-2 border-b">
                        <span class="text-gray-600">Kursus</span>
                        <span class="font-medium">{{ $invoice->course_title }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span class="text-gray-600">Total</span>
                        <span class="font-bold">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span class="text-gray-600">Referensi Gateway</span>
                        <span class="font-mono text-sm">{{ $invoice->gateway_reference ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span class="text-gray-600">Jumlah Polling Pelunasan</span>
                        <span>{{ $invoice->poll_attempts }}x</span>
                    </div>
                </div>

                @if ($invoice->isPaid())
                    <div class="mt-6">
                        <a href="{{ route('my-courses.learn', $invoice->course_id) }}"
                           class="block text-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500">
                            Buka Kursus Anda
                        </a>
                    </div>
                @else
                    <div class="mt-6 space-y-3">
                        {{-- Arahkan ke Payment Gateway DOKU Checkout --}}
                        @if ($invoice->checkout_url)
                            <a href="{{ $invoice->checkout_url }}"
                               class="block text-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500">
                                Lanjut ke Pembayaran (DOKU Checkout)
                            </a>
                        @endif

                        {{-- Check Pelunasan: polling status ke DOKU --}}
                        <form method="POST" action="{{ route('payment.poll', $invoice->id) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                                Cek Status Pembayaran (Polling)
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
