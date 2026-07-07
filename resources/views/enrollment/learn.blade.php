{{-- Halaman "belajar" — hanya bisa diakses mahasiswa yang sudah lunas --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $course->title() }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="mb-6 p-4 bg-green-50 text-green-800 rounded">
                    Selamat! Anda memiliki akses penuh ke kursus ini.
                </div>

                <h3 class="text-lg font-semibold text-gray-800">Materi Kursus</h3>
                <p class="mt-2 text-gray-700 whitespace-pre-line">{{ $course->description() }}</p>

                <div class="mt-6 space-y-3">
                    <div class="p-4 border rounded">Modul 1: Pengantar</div>
                    <div class="p-4 border rounded">Modul 2: Konsep Inti</div>
                    <div class="p-4 border rounded">Modul 3: Studi Kasus</div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('my-courses.index') }}" class="text-gray-600 hover:underline">&larr; Kembali ke Kursus Saya</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
