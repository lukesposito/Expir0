<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestione Utenti del Sistema
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4" x-data="usersManager()" x-init="init()">

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <div class="w-full md:w-1/2 relative">
                    <input type="text" x-model="searchQuery" placeholder="Cerca per nome o email..." 
                           class="w-full pl-4 pr-10 py-3 bg-white border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition shadow-sm">
                    <div x-show="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                        <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                </div>
                
                <button @click="openCreateModal()" class="w-full md:w-auto btn-add-custom">
                    <svg class="inline-block h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Nuovo Utente
                </button>
            </div>

            <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Utente</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Ruolo</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Accesso</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Notifiche</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="user in filteredUsers" :key="user.id">
                                <tr class="hover:bg-gray-50 transition" :class="{'opacity-60 bg-red-50': !user.is_active}">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0 rounded-full flex items-center justify-center text-white font-bold shadow-sm"
                                                 :class="user.role === 'admin' ? 'bg-gradient-to-br from-indigo-500 to-indigo-700' : 'bg-gradient-to-br from-gray-400 to-gray-500'">
                                                <span x-text="user.name.charAt(0).toUpperCase()"></span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900" x-text="user.name"></div>
                                                <div class="text-sm text-gray-500" x-text="user.email"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider shadow-sm"
                                              :class="user.role === 'admin' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-gray-100 text-gray-800 border border-gray-200'"
                                              x-text="user.role">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-xs font-black uppercase tracking-wider" :class="user.is_active ? 'text-green-600' : 'text-red-600'" x-text="user.is_active ? 'Attivo' : 'Bloccato'"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-xs font-black uppercase tracking-wider" :class="user.receives_notifications ? 'text-blue-600' : 'text-gray-400'" x-text="user.receives_notifications ? 'Abilitate' : 'Spente'"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-3">
                                        <button @click="openEditModal(user)" class="text-indigo-600 hover:text-indigo-900 font-bold text-sm bg-indigo-50 px-3 py-2 rounded-lg transition hover:bg-indigo-100 border border-indigo-100 shadow-sm">
                                            Modifica / Impostazioni
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            
                            <tr x-show="filteredUsers.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">Nessun utente trovato.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="showUserModal" x-cloak @keydown.escape.window="closeUserModal()" class="modal-overlay" :class="{ 'modal-active': showUserModal && !isClosingModal, 'modal-out': isClosingModal }">
                <div @click="closeUserModal()" class="modal-backdrop"></div>
                <div class="modal-container">
                    <div @click.stop class="modal-content-wrapper">
                        <div class="modal-header">
                            <h3 class="modal-title" x-text="isEditing ? 'Modifica Impostazioni Utente' : 'Nuovo Utente'"></h3>
                            <button @click="closeUserModal()" class="modal-close"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>

                        <form @submit.prevent="submitForm" class="modal-body max-h-[80vh] overflow-y-auto">
                            <div class="space-y-5">
                                <div><label class="form-label">Nome Completo *</label><input type="text" x-model="formData.name" required class="form-input"></div>
                                <div><label class="form-label">Indirizzo Email *</label><input type="email" x-model="formData.email" required class="form-input"></div>
                                <div><label class="form-label">Data di Nascita *</label><input type="date" x-model="formData.data_nascita" required class="form-input"></div>
                                <div>
                                    <label class="form-label">Ruolo *</label>
                                    <select x-model="formData.role" required class="form-input">
                                        <option value="user">Utente Standard</option>
                                        <option value="admin">Amministratore</option>
                                    </select>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                    <label class="form-label mb-1" x-text="isEditing ? 'Nuova Password (lascia vuoto per non cambiarla)' : 'Password *'"></label>
                                    <input type="password" x-model="formData.password" :required="!isEditing" class="form-input" minlength="8">
                                </div>

                                <div class="mt-6 border-t border-gray-200 pt-5">
                                    <h4 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wider">Pannello di Controllo Admin</h4>
                                    
                                    <div class="flex items-center justify-between bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-3">
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm">Accesso al Sistema</p>
                                            <p class="text-xs text-gray-500">Se disattivato, l'utente non potrà fare il login.</p>
                                        </div>
                                        <label class="switch" :class="{'opacity-50 pointer-events-none': formData.id === {{ auth()->id() }} }">
                                            <input type="checkbox" x-model="formData.is_active" :disabled="formData.id === {{ auth()->id() }}">
                                            <span class="slider slider-green"></span>
                                        </label>
                                    </div>

                                    <div class="flex items-center justify-between bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm">Notifiche Email</p>
                                            <p class="text-xs text-gray-500">Invia il riepilogo scadenze a questa persona.</p>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" x-model="formData.receives_notifications">
                                            <span class="slider slider-blue"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" @click="closeUserModal()" class="btn-secondary">Annulla</button>
                                <button type="submit" class="btn-primary" :disabled="isSubmitting" x-text="isSubmitting ? 'Salvataggio...' : 'Salva Utente'"></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div x-show="showSuccessModal" x-cloak class="modal-overlay" :class="{ 'modal-active': showSuccessModal, 'modal-out': isClosingSuccess }">
                <div @click="closeSuccessModal()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
                <div class="modal-container w-full max-w-md">
                    <div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="p-6 text-center pt-8 pb-6">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full mb-6" :class="{ 'bg-green-100': successType === 'success', 'bg-red-100': successType === 'error' }">
                                <svg x-show="successType === 'success'" class="h-10 w-10 text-green-600 animate-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <svg x-show="successType === 'error'" class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">
                                <span x-show="successType === 'success'">Operazione completata!</span>
                                <span x-show="successType === 'error'">Errore</span>
                            </h3>
                            <p class="text-gray-600 text-lg mb-6" x-text="successMessage"></p>
                            <button @click="closeSuccessModal()" class="px-6 py-2.5 rounded-lg font-semibold text-white shadow-lg transition transform hover:scale-105" :class="successType === 'success' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'">OK</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function usersManager() {
            return {
                users: @json($users),
                searchQuery: '',
                
                showUserModal: false, isClosingModal: false,
                showSuccessModal: false, isClosingSuccess: false,
                
                isEditing: false, isSubmitting: false,
                successMessage: '', successType: 'success',
                
                // Form aggiornato coi nuovi campi
                formData: { id: null, name: '', email: '', role: 'user', password: '', data_nascita: '', is_active: true, receives_notifications: true },

                get filteredUsers() {
                    if (!this.searchQuery) return this.users;
                    const q = this.searchQuery.toLowerCase();
                    return this.users.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q));
                },

                openCreateModal() { 
                    this.isEditing = false; 
                    this.formData = { id: null, name: '', email: '', role: 'user', password: '', data_nascita: '', is_active: true, receives_notifications: true }; 
                    this.showUserModal = true; 
                },
                openEditModal(user) { 
                    this.isEditing = true; 
                    this.formData = { 
                        id: user.id, 
                        name: user.name, 
                        email: user.email, 
                        role: user.role, 
                        password: '', 
                        data_nascita: user.data_nascita || '',
                        is_active: user.is_active == 1 || user.is_active == true, // Forza cast booleano
                        receives_notifications: user.receives_notifications == 1 || user.receives_notifications == true
                    }; 
                    this.showUserModal = true; 
                },
                closeUserModal() { this.isClosingModal = true; setTimeout(() => { this.showUserModal = false; this.isClosingModal = false; }, 300); },
                
                closeSuccessModal() { this.isClosingSuccess = true; setTimeout(() => { this.showSuccessModal = false; this.isClosingSuccess = false; }, 300); },
                showSuccess(msg, type = 'success') {
                    this.successMessage = msg; this.successType = type; this.showSuccessModal = true;
                    setTimeout(() => { if(this.showSuccessModal) this.closeSuccessModal(); }, 3000);
                },

                async refreshData() {
                    try {
                        const res = await fetch('{{ route("admin.users.index") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }});
                        if (res.ok) {
                            const data = await res.json();
                            this.users = data.users;
                        }
                    } catch (error) { console.error(error); }
                },

                async submitForm() {
                    this.isSubmitting = true;
                    const url = this.isEditing ? `/admin/users/${this.formData.id}` : '{{ route("admin.users.store") }}';
                    const data = new FormData();
                    data.append('_token', '{{ csrf_token() }}');
                    if (this.isEditing) data.append('_method', 'PUT');
                    
                    data.append('name', this.formData.name);
                    data.append('email', this.formData.email);
                    data.append('role', this.formData.role);
                    // Passiamo i booleani convertiti in 1 o 0 per il backend
                    data.append('is_active', this.formData.is_active ? '1' : '0');
                    data.append('receives_notifications', this.formData.receives_notifications ? '1' : '0');

                    if (this.formData.data_nascita) data.append('data_nascita', this.formData.data_nascita);
                    if (this.formData.password) data.append('password', this.formData.password);

                    try {
                        const response = await fetch(url, { method: 'POST', body: data, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }});
                        const resData = await response.json();

                        if (response.ok) {
                            this.closeUserModal();
                            await this.refreshData();
                            this.showSuccess(resData.message, 'success');
                        } else {
                            this.showSuccess(resData.message || 'Errore di validazione', 'error');
                        }
                    } catch (err) {
                        this.showSuccess('Errore di connessione', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        
        .btn-add-custom { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: white; font-weight: 600; font-size: 1rem; padding: 0.875rem 1.75rem; border-radius: 0.75rem; border: none; cursor: pointer; box-shadow: 0 4px 14px -2px rgba(79, 70, 229, 0.4); transition: all 0.25s ease; }
        .btn-add-custom:hover { transform: translateY(-2px); box-shadow: 0 8px 20px -2px rgba(79, 70, 229, 0.5); }
        .btn-primary { background: #4f46e5; color: white; padding: 0.6rem 1.5rem; border-radius: 0.5rem; font-weight: bold; transition: all 0.2s; }
        .btn-primary:hover { background: #4338ca; }
        .btn-secondary { background: #f3f4f6; color: #374151; padding: 0.6rem 1.5rem; border-radius: 0.5rem; font-weight: bold; transition: all 0.2s; }
        .btn-secondary:hover { background: #e5e7eb; }
        
        .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
        .form-input { width: 100%; padding: 0.625rem 0.875rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; transition: border-color 0.15s; }
        .form-input:focus { outline: none; border-color: #4f46e5; }

        /* Toggle Switch Universale */
        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        input:checked + .slider-green { background-color: #10b981; }
        input:checked + .slider-blue { background-color: #3b82f6; }
        input:focus + .slider { box-shadow: 0 0 1px #10b981; }
        input:checked + .slider:before { transform: translateX(24px); }

        /* Modali */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 999; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); }
        .modal-container { position: relative; width: 100%; max-width: 28rem; z-index: 1000; }
        .modal-content-wrapper { background: white; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; transform: scale(0); }
        .modal-header { background: #4f46e5; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; color: white; }
        .modal-title { font-size: 1.25rem; font-weight: bold; margin: 0; }
        .modal-close { background: transparent; border: none; cursor: pointer; transition: transform 0.2s; }
        .modal-close:hover { transform: rotate(90deg); }
        .modal-body { padding: 1.5rem; }
        .modal-footer { margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 0.75rem; }
        
        .modal-active .modal-content-wrapper { animation: blowUpModal 0.4s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-content-wrapper { animation: blowUpModalTwo 0.4s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        @keyframes blowUpModal { 0% { transform: scale(0); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        @keyframes blowUpModalTwo { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(0); opacity: 0; } }
        
        @keyframes checkmark { 0% { stroke-dashoffset: 50; stroke-dasharray: 50; } 100% { stroke-dashoffset: 0; stroke-dasharray: 50; } }
        .animate-check { stroke-dashoffset: 50; stroke-dasharray: 50; animation: checkmark 0.6s ease-in-out forwards; }
    </style>
</x-app-layout>