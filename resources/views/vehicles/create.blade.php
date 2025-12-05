<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nuovo Veicolo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('vehicles.store') }}" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Targa *</label>
                            <input type="text" name="targa" value="{{ old('targa') }}" required class="mt-1 block w-full rounded-md border-gray-300 uppercase">
                            @error('targa')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Marca</label>
                            <input type="text" name="marca" value="{{ old('marca') }}" class="mt-1 block w-full rounded-md border-gray-300">
                            @error('marca')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Modello</label>
                            <input type="text" name="modello" value="{{ old('modello') }}" class="mt-1 block w-full rounded-md border-gray-300">
                            @error('modello')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Salva
                            </button>
                            <a href="{{ route('vehicles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Annulla
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>