{{-- Katalog kursus untuk mahasiswa --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Katalog Kursus') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            @if (count($courses) === 0)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-500">
                    Belum ada kursus tersedia.
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($courses as $course)
                        @php($owned = in_array($course->id()->value, $ownedCourseIds, true))
                        <div class="bg-white shadow-sm rounded-lg p-6 flex flex-col">
                            <h3 class="font-semibold text-lg text-gray-800">{{ $course->title() }}</h3>
                            <p class="mt-2 text-sm text-gray-600 flex-1">
                                {{ Str::limit($course->description(), 120) }}
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="font-bold text-gray-900">{{ $course->price()->format() }}</span>
                                @if ($owned)
                                    <a href="{{ route('my-courses.learn', $course->id()->value) }}"
                                       class="px-3 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-500">
                                        Buka Kursus
                                    </a>
                                @else
                                    <a href="{{ route('catalog.show', $course->id()->value) }}"
                                       class="px-3 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-500">
                                        Lihat Detail
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
