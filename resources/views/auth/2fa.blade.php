<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-100 mb-4">
            <i class="fa-solid fa-shield-halved text-2xl text-emerald-600"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Autenticazione a Due Fattori</h2>
        <p>
            Il tuo account è super protetto! Apri la tua app Authenticator (Google Authenticator, Authy, ecc.) e inserisci il codice a 6 cifre.
        </p>
    </div>

    <form method="POST" action="{{ route('2fa.verify') }}">
        @csrf

        <div>
            <x-input-label for="otp" :value="__('Codice a 6 cifre')" />
            <x-text-input id="otp" class="block mt-1 w-full text-center tracking-[0.5em] text-3xl font-mono font-bold py-3" 
                          type="text" 
                          name="otp" 
                          required 
                          autofocus 
                          autocomplete="one-time-code" 
                          maxlength="6" />
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="text-sm text-gray-600 hover:text-gray-900 underline">
                Torna al Login
            </a>

            <x-primary-button style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                {{ __('Verifica e Accedi') }}
            </x-primary-button>
        </div>
    </form>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</x-guest-layout>