{{-- Panel Admin: daftar kursus (Bikin & kelola kursus) --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kelola Kursus') }}
            </h2>
            <a href="{{ route('admin.courses.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                + Bikin Kursus
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if (count($courses) === 0)
                    <p class="text-gray-500">Belum ada kursus. Silakan "Bikin Kursus".</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-sm text-gray-500">
                                <th class="py-2">Judul</th>
                                <th class="py-2">Harga</th>
                                <th class="py-2">Status</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            {{-- $course adalah aggregate domain Course --}}
                            @foreach ($courses as $course)
                                <tr class="text-sm">
                                    <td class="py-3 font-medium text-gray-800">{{ $course->title() }}</td>
                                    <td class="py-3">{{ $course->price()->format() }}</td>
                                    <td class="py-3">
                                        @if ($course->isPublished())
                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Published</span>
                                        @else
                                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">Draft</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right">
                                        <a href="{{ route('admin.courses.edit', $course->id()->value) }}"
                                           class="text-indigo-600 hover:underline">Edit</a>
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
