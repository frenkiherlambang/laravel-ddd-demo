{{-- Detail satu kursus + tombol "Pilih Kursus" (mulai order) --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Detail Kursus') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold text-gray-900">{{ $course->title() }}</h3>
                <p class="mt-4 text-gray-700 whitespace-pre-line">{{ $course->description() }}</p>

                <div class="mt-6 flex items-center justify-between border-t pt-6">
                    <span class="text-2xl font-bold text-gray-900">{{ $course->price()->format() }}</span>

                    @if ($alreadyOwned)
                        <a href="{{ route('my-courses.learn', $course->id()->value) }}"
                           class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500">
                            Anda sudah memiliki kursus ini &mdash; Buka
                        </a>
                    @else
                        {{-- Memilih kursus = membuat Order (POST) lalu menuju checkout --}}
                        <form method="POST" action="{{ route('checkout.start', $course->id()->value) }}">
                            @csrf
                            <x-primary-button>Pilih Kursus &amp; Checkout</x-primary-button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('catalog.index') }}" class="text-gray-600 hover:underline">&larr; Kembali ke katalog</a>
            </div>
        </div>
    </div>
</x-app-layout>
