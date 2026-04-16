<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Il Mio Profilo') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="profileManager()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-8 bg-gradient-to-b from-gray-50 to-white">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-8 bg-gradient-to-b from-gray-50 to-white">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-8 bg-gradient-to-b from-gray-50 to-white">
                    @include('profile.partials.update-mfa-form')
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-8 bg-gradient-to-b from-gray-50 to-white">
                    @include('profile.partials.update-passkey-form')
                </div>
            </div>

            <div x-show="showSuccessModal" x-cloak class="modal-overlay" :class="{ 'modal-active': showSuccessModal && !isClosingSuccess, 'modal-out': isClosingSuccess }">
                <div @click="closeSuccessModal()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
                <div class="modal-container w-full max-w-md">
                    <div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="p-6 text-center pt-8 pb-6">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-emerald-50 mb-5 animate-pulse">
                                <i class="fa-solid fa-check text-3xl text-emerald-500"></i>
                            </div>
                            <h3 class="text-2xl font-black text-slate-800 mb-2">Perfetto!</h3>
                            <p class="text-sm text-slate-600" x-text="successMessage"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="showErrorModal" x-cloak class="modal-overlay" :class="{ 'modal-active': showErrorModal && !isClosingError, 'modal-out': isClosingError }">
                <div @click="closeErrorModal()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
                <div class="modal-container w-full max-w-md">
                    <div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="p-6 text-center pt-8 pb-6">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-5">
                                <i class="fa-solid fa-triangle-exclamation text-3xl text-red-500"></i>
                            </div>
                            <h3 class="text-2xl font-black text-slate-800 mb-2">Attenzione</h3>
                            <p class="text-sm text-slate-600" x-html="errorMessage"></p>
                            <button @click="closeErrorModal()" class="mt-6 px-6 py-2 bg-red-500 text-white rounded-lg font-bold hover:bg-red-600 transition">Chiudi</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function profileManager() {
            return {
                showSuccessModal: false, showErrorModal: false,
                isClosingSuccess: false, isClosingError: false,
                successMessage: '', errorMessage: '',
                isSubmitting: false,

                // --- VARIABILI 2FA ---
                mfaEnabled: {{ auth()->user()->google2fa_enabled ? 'true' : 'false' }},
                mfaSetup: false,
                qrCodeSvg: '',
                secretKey: '',
                otp: '',

                closeSuccessModal() { this.isClosingSuccess = true; setTimeout(() => { this.showSuccessModal = false; this.isClosingSuccess = false; }, 300); },
                closeErrorModal() { this.isClosingError = true; setTimeout(() => { this.showErrorModal = false; this.isClosingError = false; }, 300); },
                
                showSuccess(message) { 
                    this.successMessage = message; 
                    this.showSuccessModal = true; 
                    setTimeout(() => { if(this.showSuccessModal) this.closeSuccessModal(); }, 2500); 
                },
                showError(message) { 
                    this.errorMessage = message; 
                    this.showErrorModal = true; 
                },

                async updateProfile(event) {
                    this.isSubmitting = true;
                    const form = event.target;
                    const formData = new FormData(form);
                    formData.set('_method', 'PATCH');
                    
                    try {
                        const response = await fetch('{{ route("profile.update") }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        
                        if (response.ok) {
                            this.showSuccess(data.message || 'Profilo aggiornato con successo!');
                        } else if (response.status === 422) { 
                            let errors = Object.values(data.errors).map(e => e.join(', ')).join('<br>');
                            this.showError('<strong>Errori:</strong><br>' + errors);
                        } else {
                            this.showError(data.message || 'Errore imprevisto.');
                        }
                    } catch (error) {
                        this.showError('Errore di connessione al server.');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async updatePassword(event) {
                    this.isSubmitting = true;
                    const form = event.target;
                    const formData = new FormData(form);
                    formData.set('_method', 'PUT');
                    
                    try {
                        const response = await fetch('{{ route("password.update") }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        
                        if (response.ok) {
                            form.reset();
                            this.showSuccess(data.message || 'Password aggiornata con sicurezza!');
                        } else if (response.status === 422) { 
                            let errors = Object.values(data.errors).map(e => e.join(', ')).join('<br>');
                            this.showError('<strong>Controlla la password:</strong><br>' + errors);
                        } else {
                            this.showError(data.message || 'Errore imprevisto.');
                        }
                    } catch (error) {
                        this.showError('Errore di connessione al server.');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                // --- FUNZIONI 2FA ---
                async generate2FA() {
                    this.isSubmitting = true;
                    try {
                        const response = await fetch('{{ route("2fa.generate") }}', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const data = await response.json();
                        if(response.ok) {
                            this.qrCodeSvg = data.qr_code;
                            this.secretKey = data.secret;
                            this.mfaSetup = true;
                        } else {
                            this.showError('Impossibile generare il QR Code.');
                        }
                    } catch(e) {
                        this.showError('Errore di connessione.');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async confirm2FA() {
                    this.isSubmitting = true;
                    try {
                        const formData = new FormData();
                        formData.append('otp', this.otp);
                        formData.append('_token', '{{ csrf_token() }}');

                        const response = await fetch('{{ route("2fa.confirm") }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if(response.ok) {
                            this.mfaEnabled = true;
                            this.mfaSetup = false;
                            this.otp = '';
                            this.showSuccess(data.message);
                        } else {
                            let err = data.errors?.otp ? data.errors.otp[0] : 'Codice errato. Riprova.';
                            this.showError(err);
                        }
                    } catch(e) {
                        this.showError('Errore di connessione.');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async disable2FA() {
                    if(!confirm("Sei sicuro di voler disabilitare l'Autenticazione a Due Fattori? Il tuo account sarà meno protetto.")) return;
                    
                    this.isSubmitting = true;
                    try {
                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');

                        const response = await fetch('{{ route("2fa.disable") }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if(response.ok) {
                            this.mfaEnabled = false;
                            this.showSuccess(data.message);
                        } else {
                            this.showError('Impossibile disabilitare 2FA.');
                        }
                    } catch(e) {
                        this.showError('Errore di connessione.');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                // --- FUNZIONI PASSKEY ---
                async registerPasskey() {
                    this.isSubmitting = true;
                    try {
                        // Usiamo window.Webpass.attest
                        const { success, error } = await window.Webpass.attest("/webauthn/register/options", "/webauthn/register");
                        
                        if (success) {
                            this.showSuccess('Dispositivo registrato con successo! 🎉');
                            setTimeout(() => location.reload(), 2000); 
                        } else {
                            console.error(error);
                            this.showError('Registrazione annullata o fallita.');
                        }
                    } catch (e) {
                        console.error(e);
                        this.showError('Errore di connessione al server.');
                    } finally {
                        this.isSubmitting = false;
                    }
                }

                // Aggiungi questa funzione dentro il tuo componente Alpine (es. x-data="profileManager()")
                async revokePasskey(id) {
                    if (!confirm('Sei sicuro di voler revocare l\'accesso a questo dispositivo? Non potrai più usarlo per fare il login.')) {
                        return;
                    }

                    try {
                        const response = await fetch(`/profile/passkey/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Rimuove l'elemento dalla vista con una transizione fluida
                            const element = document.getElementById(`passkey-${id}`);
                            if (element) {
                                element.style.opacity = '0';
                                setTimeout(() => element.remove(), 300);
                            }
                            alert('Dispositivo revocato!'); // Sostituisci con il tuo Toast di successo se ne hai uno
                        } else {
                            alert(data.message || 'Errore durante la revoca.');
                        }
                    } catch (error) {
                        console.error('Errore:', error);
                        alert('Errore di connessione al server.');
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 999; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0); }
        .modal-active .modal-backdrop { animation: fadeIn 0.4s ease-out forwards; }
        .modal-out .modal-backdrop { animation: fadeOut 0.4s ease-out forwards; }
        .modal-container { position: relative; z-index: 1000; }
        .modal-content-wrapper { transform: scale(0); }
        .modal-active .modal-content-wrapper { animation: blowUpModal 0.4s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-content-wrapper { animation: blowUpModalTwo 0.4s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        @keyframes fadeIn { 0% { background: rgba(0, 0, 0, 0); } 100% { background: rgba(0, 0, 0, 0.6); } }
        @keyframes fadeOut { 0% { background: rgba(0, 0, 0, 0.6); } 100% { background: rgba(0, 0, 0, 0); } }
        @keyframes blowUpModal { 0% { transform: scale(0); } 100% { transform: scale(1); } }
        @keyframes blowUpModalTwo { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(0); opacity: 0; } }
        
        .btn-primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3); transition: all 0.2s; }
        .btn-primary:hover { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); transform: translateY(-2px); box-shadow: 0 6px 12px -2px rgba(99, 102, 241, 0.4); }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .btn-secondary { background: #f3f4f6; color: #374151; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; transition: all 0.2s; }
        .btn-secondary:hover { background: #e5e7eb; }
        .btn-danger { background: #ef4444; color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; transition: all 0.2s; }
        .btn-danger:hover { background: #dc2626; }
        .form-input { width: 100%; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 0.5rem; transition: all 0.2s; }
        .form-input:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
    </style>
</x-app-layout>