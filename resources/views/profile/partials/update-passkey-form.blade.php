<section>
    <header class="mb-6 flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
            <i class="fa-solid fa-fingerprint text-blue-600 text-xl"></i>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">
                {{ __('Accesso Biometrico (Passkey)') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Registra il tuo FaceID, TouchID o Windows Hello per accedere istantaneamente senza password.') }}
            </p>
        </div>
    </header>

    <div class="mt-6">
        <div class="bg-blue-50 rounded-2xl p-6 border border-blue-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-blue-900 mb-1">
                    Dispositivi Registrati: <span {{ $passkeys->count() }}</span>
                </h3>
                <p class="text-sm text-blue-700">Puoi registrare più dispositivi (es. il tuo smartphone e il PC).</p>
            </div>
            <button type="button" @click="registerPasskey()" class="btn-primary whitespace-nowrap shadow-md" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none;" :disabled="isSubmitting">
                <i class="fa-solid fa-plus mr-2"></i> 
                <span x-text="isSubmitting ? 'Attendi...' : 'Registra Dispositivo'"></span>
            </button>
        </div>

        <div class="mt-6 space-y-3">
            @forelse($passkeys as $pk)
                <div id="passkey-{{ $pk->id }}" class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center gap-4">
                        
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 flex-shrink-0">
                            @if(str_contains(strtolower($pk->name ?? ''), 'iphone') || str_contains(strtolower($pk->name ?? ''), 'android') || str_contains(strtolower($pk->name ?? ''), 'mobile'))
                                <i class="fa-solid fa-mobile-screen text-lg"></i>
                            @elseif(str_contains(strtolower($pk->name ?? ''), 'mac') || str_contains(strtolower($pk->name ?? ''), 'windows') || str_contains(strtolower($pk->name ?? ''), 'linux'))
                                <i class="fa-solid fa-laptop text-lg"></i>
                            @else
                                <i class="fa-solid fa-fingerprint text-lg"></i>
                            @endif
                        </div>
                        
                        <div>
                            <p class="font-bold text-gray-900 capitalize">
                                {{ $pk->alias ?? ($pk->name ?? 'Chiave di Sicurezza #' . $loop->iteration) }}
                            </p>
                            <p class="text-xs text-gray-500">
                                Aggiunta il: {{ $pk->created_at->format('d/m/Y') }} alle {{ $pk->created_at->format('H:i') }}
                            </p>
                            <p class="text-[10px] font-mono text-gray-400 mt-0.5 truncate max-w-[150px] sm:max-w-xs" title="{{ $pk->id }}">
                                ID: {{ substr($pk->id, 0, 15) }}...
                            </p>
                        </div>

                    </div>
                    <button type="button" @click="revokePasskey('{{ $pk->id }}')" class="text-red-500 hover:bg-red-50 px-3 py-2 rounded-lg text-sm font-bold transition-colors">
                        Revoca
                    </button>
                </div>
            @empty
                <div class="text-center py-6 px-4 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <i class="fa-solid fa-shield-halved text-gray-400 text-2xl mb-2"></i>
                    <p class="text-sm font-semibold text-gray-600">Nessuna Passkey configurata.</p>
                    <p class="text-xs text-gray-500 mt-1">Clicca su "Registra Dispositivo" per iniziare.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>