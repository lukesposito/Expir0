<section>
    <header class="mb-6 flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
            <i class="fa-solid fa-shield-halved text-emerald-600 text-xl"></i>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">
                {{ __('Autenticazione a Due Fattori (2FA)') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Aggiungi un ulteriore livello di sicurezza al tuo account utilizzando un\'app come Google Authenticator o Authy.') }}
            </p>
        </div>
    </header>

    <div class="mt-6">
        <div x-show="!mfaEnabled && !mfaSetup" x-cloak>
            <p class="text-sm text-gray-600 mb-4">
                Quando l'autenticazione a due fattori è abilitata, ti verrà richiesto un token sicuro a 6 cifre durante l'accesso.
            </p>
            <button @click="generate2FA()" type="button" class="btn-primary" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" :disabled="isSubmitting">
                <span x-text="isSubmitting ? 'Generazione in corso...' : 'Abilita 2FA'"></span>
            </button>
        </div>

        <div x-show="!mfaEnabled && mfaSetup" x-cloak class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
            <p class="text-sm text-slate-800 font-bold mb-4">
                1. Scansiona il seguente codice QR utilizzando l'applicazione di autenticazione sul tuo telefono.
            </p>
            
            <div class="bg-white p-4 inline-block rounded-xl shadow-sm mb-4" x-html="qrCodeSvg"></div>
            
            <p class="text-xs text-slate-500 mb-6">
                Se non puoi scansionare il codice QR, inserisci manualmente questa chiave segreta: <br>
                <strong class="font-mono text-slate-800 tracking-widest text-sm" x-text="secretKey"></strong>
            </p>

            <p class="text-sm text-slate-800 font-bold mb-3">
                2. Inserisci il codice a 6 cifre generato dall'app
            </p>
            
            <div class="max-w-xs mb-6">
                <input type="text" x-model="otp" class="form-input tracking-[0.5em] text-center text-2xl font-mono font-bold" maxlength="6" placeholder="123456" @keyup.enter="confirm2FA()">
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
                <button type="button" @click="mfaSetup = false; otp = ''" class="btn-secondary">Annulla</button>
                <button type="button" @click="confirm2FA()" class="btn-primary" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" :disabled="isSubmitting || otp.length !== 6">
                    <span x-text="isSubmitting ? 'Verifica...' : 'Conferma e Attiva'"></span>
                </button>
            </div>
        </div>

        <div x-show="mfaEnabled" x-cloak class="bg-emerald-50 rounded-2xl p-6 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-emerald-900 mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> Autenticazione a Due Fattori Attiva
                </h3>
                <p class="text-sm text-emerald-700">Il tuo account ora possiede un livello di sicurezza bancario.</p>
            </div>
            <button type="button" @click="disable2FA()" class="btn-danger whitespace-nowrap" :disabled="isSubmitting">
                <span x-text="isSubmitting ? 'Disattivazione...' : 'Disabilita 2FA'"></span>
            </button>
        </div>
    </div>
</section>