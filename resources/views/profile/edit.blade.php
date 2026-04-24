<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Il Mio Profilo') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="profileManager()" x-init="init()">
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

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-8 bg-gradient-to-b from-gray-50 to-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-mobile-screen-button text-blue-500"></i> Notifiche su questo dispositivo
                            </h3>
                            <p class="text-sm text-slate-500 mt-1">
                                Ricevi avvisi di scadenza direttamente sullo schermo del telefono o del PC.
                            </p>
                        </div>
                        
                        <button @click="togglePush()" 
                                class="px-5 py-2.5 font-bold rounded-xl text-white transition-all shadow-md flex items-center gap-2"
                                :class="isPushSubscribed ? 'bg-red-500 hover:bg-red-600' : 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800'"
                                :disabled="isPushLoading || !isPushSupported">
                            <span x-show="isPushLoading"><i class="fa-solid fa-spinner fa-spin"></i></span>
                            <span x-show="!isPushLoading && isPushSubscribed" x-cloak><i class="fa-solid fa-bell-slash"></i> Disattiva</span>
                            <span x-show="!isPushLoading && !isPushSubscribed" x-cloak><i class="fa-solid fa-bell"></i> Attiva</span>
                        </button>
                    </div>
                    
                    <div x-show="!isPushSupported" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600 font-medium flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Il tuo browser o sistema operativo non supporta le notifiche web push.
                    </div>
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
                            <p class="text-sm text-slate-600" x-html="successMessage"></p>
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

            <div x-show="showPasskeyModal" x-cloak class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div x-show="showPasskeyModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closePasskeyModal()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
                
                <div x-show="showPasskeyModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" @click.stop class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all">
                    
                    <div class="px-6 py-4 bg-gradient-to-br from-blue-500 to-blue-700 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white m-0" id="modal-title">Registra Dispositivo</h3>
                        <button @click="closePasskeyModal()" class="text-white/80 hover:text-white hover:rotate-90 transition-transform p-1 rounded-lg">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <div class="p-6">
                        <div class="mb-5 text-center">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-50 mb-3 border border-blue-100 shadow-inner">
                                <i class="fa-solid fa-fingerprint text-3xl text-blue-600"></i>
                            </div>
                            <p class="text-sm text-slate-600">
                                Scegli un nome per riconoscere facilmente questo dispositivo nella tua lista di sicurezza.
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nome Dispositivo</label>
                            <input type="text" x-model="newPasskeyName" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-800 font-medium" placeholder="es. iPhone di Marco, PC Ufficio..." @keyup.enter="confirmPasskeyRegistration()">
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                        <button type="button" @click="closePasskeyModal()" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl font-bold hover:bg-slate-50 transition-colors shadow-sm">
                            Annulla
                        </button>
                        <button type="button" @click="confirmPasskeyRegistration()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-bold hover:from-blue-700 hover:to-blue-800 transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2" :disabled="!newPasskeyName || isSubmitting">
                            <span x-text="isSubmitting ? 'Attendi...' : 'Procedi'"></span>
                            <i class="fa-solid fa-arrow-right" x-show="!isSubmitting"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Funzione helper per le Push Notifications
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

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
                showPasskeyModal: false,
                isClosingPasskey: false,
                newPasskeyName: '',

                // --- VARIABILI PUSH NOTIFICATIONS ---
                isPushSubscribed: false,
                isPushLoading: true,
                isPushSupported: ('serviceWorker' in navigator) && ('PushManager' in window),
                vapidPublicKey: '{{ config('webpush.vapid.public_key') }}',

                // Inizializzazione chiamata da x-init nel div principale
                init() {
                    this.initPush();
                },

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

                // --- FUNZIONI PUSH NOTIFICATIONS ---
                async initPush() {
                    if (!this.isPushSupported) {
                        this.isPushLoading = false;
                        return;
                    }
                    try {
                        const registration = await navigator.serviceWorker.register('/sw.js');
                        const subscription = await registration.pushManager.getSubscription();
                        this.isPushSubscribed = !(subscription === null);
                    } catch (e) {
                        console.error('Errore Service Worker:', e);
                    } finally {
                        this.isPushLoading = false;
                    }
                },

                async togglePush() {
                    if (this.isPushSubscribed) {
                        await this.unsubscribePush();
                    } else {
                        await this.subscribePush();
                    }
                },

                async subscribePush() {
                    this.isPushLoading = true;
                    try {
                        const permission = await Notification.requestPermission();
                        if (permission !== 'granted') {
                            this.showError('Devi consentire le notifiche nel browser per poterle attivare.');
                            this.isPushLoading = false;
                            return;
                        }

                        const registration = await navigator.serviceWorker.ready;
                        const convertedVapidKey = urlBase64ToUint8Array(this.vapidPublicKey);

                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: convertedVapidKey
                        });

                        const response = await fetch('{{ route('push.subscribe') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(subscription)
                        });

                        if (response.ok) {
                            this.isPushSubscribed = true;
                            this.showSuccess('Notifiche attivate con successo sul tuo dispositivo! 🎉');
                        } else {
                            this.showError('Errore di comunicazione col server.');
                        }
                    } catch (e) {
                        console.error('Errore Iscrizione:', e);
                        this.showError('Errore durante l\'attivazione. Controlla che il sito sia sicuro (HTTPS).');
                    } finally {
                        this.isPushLoading = false;
                    }
                },

                async unsubscribePush() {
                    this.isPushLoading = true;
                    try {
                        const registration = await navigator.serviceWorker.ready;
                        const subscription = await registration.pushManager.getSubscription();
                        
                        if (subscription) {
                            await fetch('{{ route('push.unsubscribe') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ endpoint: subscription.endpoint })
                            });
                            
                            await subscription.unsubscribe();
                            this.isPushSubscribed = false;
                            this.showSuccess('Notifiche disattivate per questo dispositivo.');
                        }
                    } catch (e) {
                        console.error('Errore Disiscrizione:', e);
                        this.showError('Impossibile disattivare le notifiche.');
                    } finally {
                        this.isPushLoading = false;
                    }
                },

                // --- FUNZIONI PROFILO E PASSWORD ---
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
                    this.isSubmitting = false;
                    const ua = window.navigator.userAgent;
                    let suggestedName = "Mio PC";
                    if (/iPhone|iPad|iPod/i.test(ua)) suggestedName = "Mio iPhone";
                    else if (/Android/i.test(ua)) suggestedName = "Mio Android";
                    else if (/Macintosh/i.test(ua)) suggestedName = "Mio Mac";

                    this.newPasskeyName = suggestedName;
                    this.showPasskeyModal = true;
                },

                async confirmPasskeyRegistration() {
                    if (!this.newPasskeyName) return;
                    
                    this.isSubmitting = true;
                    this.showPasskeyModal = false;

                    try {
                        const registerUrl = "/webauthn/register?name=" + encodeURIComponent(this.newPasskeyName);
                        const { success, error } = await window.Webpass.attest("/webauthn/register/options", registerUrl);
                        
                        if (success) {
                            this.showSuccess('Dispositivo "' + this.newPasskeyName + '" registrato! 🎉');
                            setTimeout(() => location.reload(), 2000); 
                        } else {
                            this.showError('Registrazione fallita o annullata.');
                        }
                    } catch (e) {
                        this.showError('Errore di connessione al server.');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                closePasskeyModal() {
                    this.isClosingPasskey = true;
                    this.isSubmitting = false;
                    this.showPasskeyModal = false; 
                    this.isClosingPasskey = false; 
                },

                async revokePasskey(id) {
                    if (!confirm('Sei sicuro di voler revocare l\'accesso a questo dispositivo? Non potrai più usarlo per fare il login.')) return;

                    try {
                        const response = await fetch(`/profile/passkey/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            const element = document.getElementById(`passkey-${id}`);
                            if (element) {
                                element.style.opacity = '0';
                                setTimeout(() => element.remove(), 300);
                            }
                            this.showSuccess('Dispositivo revocato!');
                        } else {
                            this.showError(data.message || 'Errore durante la revoca.');
                        }
                    } catch (error) {
                        this.showError('Errore di connessione al server.');
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