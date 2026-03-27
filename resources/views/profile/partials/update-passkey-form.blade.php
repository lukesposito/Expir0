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
                    Dispositivi Registrati: {{ auth()->user()->webAuthnCredentials()->count() ?? 0 }}
                </h3>
                <p class="text-sm text-blue-700">Puoi registrare più dispositivi (es. il tuo smartphone e il tuo PC).</p>
            </div>
            <button type="button" @click="registerPasskey()" class="btn-primary whitespace-nowrap shadow-md" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none;" :disabled="isSubmitting">
                <i class="fa-solid fa-plus mr-2"></i> 
                <span x-text="isSubmitting ? 'Attendi...' : 'Registra Dispositivo'"></span>
            </button>
        </div>
    </div>
</section>