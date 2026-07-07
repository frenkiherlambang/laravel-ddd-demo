{{-- Kursus yang dimiliki mahasiswa (hasil pelunasan/Enrollment) --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kursus Saya') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (count($courses) === 0)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-500">
                    Anda belum memiliki kursus. Beli kursus dari
                    <a href="{{ route('catalog.index') }}" class="text-indigo-600 hover:underline">katalog</a>.
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($courses as $course)
                        <div class="bg-white shadow-sm rounded-lg p-6 flex flex-col">
                            <h3 class="font-semibold text-lg text-gray-800">{{ $course->title() }}</h3>
                            <p class="mt-2 text-sm text-gray-600 flex-1">{{ Str::limit($course->description(), 100) }}</p>
                            <a href="{{ route('my-courses.learn', $course->id()->value) }}"
                               class="mt-4 text-center px-3 py-2 bg-green-600 text-white rounded hover:bg-green-500">
                                Belajar
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
