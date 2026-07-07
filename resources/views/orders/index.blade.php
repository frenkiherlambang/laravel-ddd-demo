{{-- Daftar pesanan mahasiswa --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Pesanan Saya') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if (count($orders) === 0)
                    <p class="text-gray-500">Belum ada pesanan.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-sm text-gray-500">
                                <th class="py-2">Kursus</th>
                                <th class="py-2">Total</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            {{-- $order = aggregate domain Order --}}
                            @foreach ($orders as $order)
                                <tr class="text-sm">
                                    <td class="py-3 font-medium text-gray-800">{{ $order->courseTitle() }}</td>
                                    <td class="py-3">{{ $order->amount()->format() }}</td>
                                    <td class="py-3">
                                        @php($status = $order->status()->value)
                                        <span class="px-2 py-1 text-xs rounded
                                            @if ($status === 'paid') bg-green-100 text-green-700
                                            @elseif ($status === 'cancelled') bg-red-100 text-red-700
                                            @else bg-yellow-100 text-yellow-700 @endif">
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
