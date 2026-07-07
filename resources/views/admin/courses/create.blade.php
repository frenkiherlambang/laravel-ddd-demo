{{-- Form Admin: buat kursus baru --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Bikin Kursus') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.courses.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="title" value="Judul Kursus" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                      :value="old('title')" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Deskripsi" />
                        <textarea id="description" name="description" rows="5"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="price" value="Harga (Rupiah)" />
                        <x-text-input id="price" name="price" type="number" min="0" class="mt-1 block w-full"
                                      :value="old('price', 0)" required />
                        <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>

                    <label class="inline-flex items-center">
                        <input type="checkbox" name="published" value="1" checked
                               class="rounded border-gray-300 text-indigo-600">
                        <span class="ms-2 text-sm text-gray-600">Publikasikan langsung ke katalog</span>
                    </label>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Simpan</x-primary-button>
                        <a href="{{ route('admin.courses.index') }}" class="text-gray-600 hover:underline">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
