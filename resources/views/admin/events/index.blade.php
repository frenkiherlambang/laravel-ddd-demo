{{-- Panel Admin: EVENT STORE — riwayat fakta domain dari tabel stored_events --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Event Store') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Penjelasan singkat konteks Event Sourcing --}}
            <div class="mb-4 bg-indigo-50 border border-indigo-100 text-indigo-800 rounded-md p-4 text-sm">
                Setiap baris adalah <strong>fakta domain</strong> yang direkam permanen di
                <code class="bg-indigo-100 px-1 rounded">stored_events</code> (write-side).
                Read model <code class="bg-indigo-100 px-1 rounded">invoices</code> hanyalah proyeksi
                turunan dari stream ini — bisa dibangun ulang kapan saja.
            </div>

            {{-- Filter per aggregate (Invoice UUID) --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[240px]">
                        <label for="aggregate" class="block text-sm font-medium text-gray-700 mb-1">
                            Filter per Aggregate UUID (Invoice)
                        </label>
                        <input id="aggregate" type="text" name="aggregate"
                               value="{{ $aggregate }}"
                               placeholder="tempel UUID invoice…"
                               class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm font-mono">
                    </div>
                    <x-primary-button class="!py-2">Filter</x-primary-button>
                    @if ($aggregate !== '')
                        <a href="{{ route('admin.events.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-md hover:bg-gray-200">
                            Reset
                        </a>
                    @endif
                </form>

                @if ($events->isEmpty())
                    <p class="text-gray-500">
                        @if ($aggregate !== '')
                            Tidak ada event untuk aggregate tersebut.
                        @else
                            Event store masih kosong. Jalankan alur checkout untuk merekam event.
                        @endif
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                                    <th class="py-2 pr-4">#</th>
                                    <th class="py-2 pr-4">Waktu</th>
                                    <th class="py-2 pr-4">Aggregate</th>
                                    <th class="py-2 pr-4">Versi</th>
                                    <th class="py-2 pr-4">Event</th>
                                    <th class="py-2 pr-4">Properti</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($events as $event)
                                    @php
                                        [$bg, $fg] = $typeColor($event->event_class);
                                    @endphp
                                    <tr class="text-sm align-top">
                                        <td class="py-3 pr-4 font-mono text-gray-400">{{ $event->id }}</td>
                                        <td class="py-3 pr-4 whitespace-nowrap text-gray-600">
                                            {{ \Illuminate\Support\Carbon::parse($event->created_at)->format('d M H:i:s') }}
                                        </td>
                                        <td class="py-3 pr-4 font-mono text-xs text-gray-500">
                                            <a href="{{ route('admin.events.index', ['aggregate' => $event->aggregate_uuid]) }}"
                                               class="hover:text-indigo-600 hover:underline"
                                               title="{{ $event->aggregate_uuid }}">
                                                {{ \Illuminate\Support\Str::limit($event->aggregate_uuid ?? '-', 8) }}
                                            </a>
                                        </td>
                                        <td class="py-3 pr-4 font-mono text-gray-600">{{ $event->aggregate_version ?? '-' }}</td>
                                        <td class="py-3 pr-4">
                                            <span class="inline-block px-2 py-1 text-xs rounded {{ $bg }} {{ $fg }}">
                                                {{ $typeLabel($event->event_class) }}
                                            </span>
                                        </td>
                                        <td class="py-3 pr-4">
                                            <dl class="grid grid-cols-1 gap-x-4 gap-y-0.5 text-xs">
                                                @foreach ($event->event_properties ?? [] as $key => $value)
                                                    <div class="flex gap-2">
                                                        <dt class="font-mono text-gray-400">{{ $key }}</dt>
                                                        <dd class="font-medium text-gray-700 break-all">
                                                            @if (is_array($value))
                                                                <code class="text-gray-500">{{ json_encode($value) }}</code>
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $events->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
