<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modifica Scadenza
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('deadlines.update', $deadline) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo</label>
                            <select name="tipo" id="tipo_scadenza_edit" required class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="bollo" {{ $deadline->tipo == 'bollo' ? 'selected' : '' }}>Bollo</option>
                                <option value="assicurazione" {{ $deadline->tipo == 'assicurazione' ? 'selected' : '' }}>Assicurazione</option>
                                <option value="patente" {{ $deadline->tipo == 'patente' ? 'selected' : '' }}>Patente</option>
                            </select>
                            @error('tipo')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        
                        <div id="vehicle_select_edit" style="display:{{ $deadline->tipo === 'patente' ? 'none' : 'block' }};">
                            <label class="block text-sm font-medium text-gray-700">Veicolo</label>
                            <select name="vehicle_id" id="vehicle_id_edit" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">Seleziona veicolo</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}" {{ $deadline->vehicle_id == $v->id ? 'selected' : '' }}>
                                        {{ $v->targa }} - {{ $v->marca }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        
                        <div id="user_select_edit" style="display:{{ $deadline->tipo === 'patente' ? 'block' : 'none' }};">
                            <label class="block text-sm font-medium text-gray-700">Persona</label>
                            <select name="user_id" id="user_id_edit" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">Seleziona persona</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ $deadline->user_id == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data Scadenza</label>
                            <input type="date" name="data_scadenza" value="{{ $deadline->data_scadenza->format('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300">
                            @error('data_scadenza')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Importo (€)</label>
                            <input type="number" name="importo" value="{{ $deadline->importo }}" step="0.01" class="mt-1 block w-full rounded-md border-gray-300">
                            @error('importo')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Note</label>
                            <textarea name="note" rows="3" class="mt-1 block w-full rounded-md border-gray-300">{{ $deadline->note }}</textarea>
                            @error('note')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Salva
                            </button>
                            <a href="{{ route('deadlines.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Annulla
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.getElementById('tipo_scadenza_edit').addEventListener('change', function() {
        const tipo = this.value;
        const vehicleSelect = document.getElementById('vehicle_select_edit');
        const userSelect = document.getElementById('user_select_edit');
        const vehicleInput = document.getElementById('vehicle_id_edit');
        const userInput = document.getElementById('user_id_edit');
        
        if (tipo === 'patente') {
            vehicleSelect.style.display = 'none';
            userSelect.style.display = 'block';
            vehicleInput.removeAttribute('required');
            vehicleInput.value = '';
            userInput.setAttribute('required', 'required');
        } else {
            vehicleSelect.style.display = 'block';
            userSelect.style.display = 'none';
            vehicleInput.setAttribute('required', 'required');
            userInput.removeAttribute('required');
            userInput.value = '';
        }
    });
    </script>
</x-app-layout>