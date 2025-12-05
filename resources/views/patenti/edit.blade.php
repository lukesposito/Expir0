<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modifica Patente
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('patenti.update', $patente) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Numero Patente *</label>
                            <input type="text" name="numero_patente" value="{{ $patente->numero_patente }}" required class="mt-1 block w-full rounded-md border-gray-300 uppercase">
                            @error('numero_patente')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo (es: B, A, C)</label>
                            <input type="text" name="tipo" value="{{ $patente->tipo }}" class="mt-1 block w-full rounded-md border-gray-300 uppercase" maxlength="10">
                            @error('tipo')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data Rilascio</label>
                            <input type="date" name="data_rilascio" value="{{ $patente->data_rilascio ? $patente->data_rilascio->format('Y-m-d') : '' }}" class="mt-1 block w-full rounded-md border-gray-300">
                            @error('data_rilascio')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Salva
                            </button>
                            <a href="{{ route('patenti.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Annulla
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>