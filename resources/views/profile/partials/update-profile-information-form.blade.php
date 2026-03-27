<section>
    <header class="mb-6 flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
            <i class="fa-solid fa-user text-indigo-600 text-xl"></i>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">
                {{ __('Informazioni Personali') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __("Aggiorna il nome e l'indirizzo email associato al tuo account.") }}
            </p>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form @submit.prevent="updateProfile($event)" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Nome Completo') }}</label>
                <input id="name" name="name" type="text" class="form-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Email') }}</label>
                <input id="email" name="email" type="email" class="form-input" value="{{ old('email', $user->email) }}" required autocomplete="username" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-3 bg-amber-50 p-3 rounded-lg border border-amber-200">
                        <p class="text-sm text-amber-800 font-medium">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ __('La tua email non è verificata.') }}
                            <button form="send-verification" class="underline text-amber-900 hover:text-amber-700 focus:outline-none">
                                {{ __('Clicca qui per rimandare l\'email di verifica.') }}
                            </button>
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
            <button type="submit" class="btn-primary" :disabled="isSubmitting">
                <span x-text="isSubmitting ? 'Salvataggio...' : '{{ __('Salva Modifiche') }}'"></span>
            </button>
        </div>
    </form>
</section>