{{-- Ringkasan checkout sebelum membayar (Order -> Checkout -> Bayar) --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Checkout') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Pesanan</h3>

                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Kursus</span>
                    <span class="font-medium text-gray-900">{{ $order->courseTitle() }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Status Order</span>
                    <span class="font-medium text-gray-900">{{ $order->status()->value }}</span>
                </div>
                <div class="flex justify-between py-4 text-lg">
                    <span class="font-semibold">Total</span>
                    <span class="font-bold">{{ $order->amount()->format() }}</span>
                </div>

                {{-- "Bayar": checkout order + buat invoice pending + mulai sesi DOKU --}}
                <form method="POST" action="{{ route('checkout.pay', $orderId) }}" class="mt-4">
                    @csrf
                    <x-primary-button class="w-full justify-center">
                        Bayar Sekarang
                    </x-primary-button>
                </form>

                <p class="mt-3 text-xs text-gray-500">
                    Menekan "Bayar" akan membuat Invoice berstatus <strong>pending</strong> lalu
                    mengarahkan Anda ke halaman pembayaran (Payment Gateway DOKU).
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
