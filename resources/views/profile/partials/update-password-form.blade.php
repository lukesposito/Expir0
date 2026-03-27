<section>
    <header class="mb-6 flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
            <i class="fa-solid fa-lock text-purple-600 text-xl"></i>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">
                {{ __('Aggiorna Password') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Assicurati di usare una password lunga e sicura per proteggere il tuo account.') }}
            </p>
        </div>
    </header>

    <form @submit.prevent="updatePassword($event)" class="space-y-6">
        @csrf
        
        <div class="max-w-xl">
            <label for="update_password_current_password" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Password Attuale') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-input" autocomplete="current-password" required />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="update_password_password" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Nuova Password') }}</label>
                <input id="update_password_password" name="password" type="password" class="form-input" autocomplete="new-password" required minlength="8" />
            </div>

            <div>
                <label for="update_password_password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Conferma Nuova Password') }}</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-input" autocomplete="new-password" required minlength="8" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
            <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);" :disabled="isSubmitting">
                <span x-text="isSubmitting ? 'Aggiornamento in corso...' : '{{ __('Aggiorna Password') }}'"></span>
            </button>
        </div>
    </form>
</section>