<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestione Utenti del Sistema
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4" 
             x-data="usersManager()"
             x-init="init()">

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
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="user in filteredUsers" :key="user.id">
                                <tr class="hover:bg-gray-50 transition" :class="{'opacity-60 bg-gray-50': user.is_deleted}">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0 rounded-full flex items-center justify-center text-white font-bold"
                                                 :class="user.role === 'admin' ? 'bg-indigo-600' : 'bg-gray-400'">
                                                <span x-text="user.name.charAt(0).toUpperCase()"></span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900" x-text="user.name"></div>
                                                <div class="text-sm text-gray-500" x-text="user.email"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wider"
                                              :class="user.role === 'admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800'"
                                              x-text="user.role">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <label class="switch switch-sm" :class="{'opacity-50 pointer-events-none': user.id === {{ auth()->id() }} }">
                                                <input type="checkbox" :checked="!user.is_deleted" @change="toggleUserStatus(user)" :disabled="user.id === {{ auth()->id() }}">
                                                <span class="slider"></span>
                                            </label>
                                            <span class="text-xs font-bold" :class="user.is_deleted ? 'text-red-500' : 'text-green-500'" x-text="user.is_deleted ? 'Disabilitato' : 'Attivo'"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-3">
                                        <button @click="openEditModal(user)" class="text-indigo-600 hover:text-indigo-900 font-bold text-sm bg-indigo-50 px-3 py-2 rounded-lg transition hover:bg-indigo-100">
                                            Modifica
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            
                            <tr x-show="filteredUsers.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">Nessun utente trovato.</td>
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
                            <h3 class="modal-title" x-text="isEditing ? 'Modifica Utente' : 'Nuovo Utente'"></h3>
                            <button @click="closeUserModal()" class="modal-close"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>

                        <form @submit.prevent="submitForm" class="modal-body">
                            <div class="space-y-5">
                                <div>
                                    <label class="form-label">Nome Completo *</label>
                                    <input type="text" x-model="formData.name" required class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Indirizzo Email *</label>
                                    <input type="email" x-model="formData.email" required class="form-input">
                                </div>
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
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" @click="closeUserModal()" class="btn-secondary">Annulla</button>
                                <button type="submit" class="btn-primary" :disabled="isSubmitting" x-text="isSubmitting ? 'Salvataggio...' : 'Salva Utente'"></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div x-show="showNotification" x-cloak @keydown.escape.window="showNotification = false" class="modal-overlay" style="z-index: 1070;">
                <div @click="showNotification = false" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
                <div class="modal-container w-full max-w-sm" x-transition>
                    <div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl p-8 text-center border-t-8" :class="notificationType === 'success' ? 'border-green-500' : 'border-red-500'">
                        <h3 class="text-2xl font-bold mb-2" :class="notificationType === 'success' ? 'text-green-600' : 'text-red-600'" x-text="notificationType === 'success' ? 'Operazione Riuscita!' : 'Attenzione!'"></h3>
                        <p class="text-gray-600 text-lg mb-6" x-text="notificationMessage"></p>
                        <button @click="showNotification = false" class="w-full py-3 rounded-lg font-bold text-white transition transform hover:scale-105" :class="notificationType === 'success' ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600'">OK</button>
                    </div>
                </div>
            </div>

            <div x-show="showConfirmModal" x-cloak class="modal-overlay" :class="{ 'modal-active': showConfirmModal, 'modal-out': isClosingConfirm }">
                <div @click="closeConfirmModal()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>
                <div class="modal-container w-full max-w-md">
                    <div @click.stop class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                        <div class="p-6 text-center pt-8">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-4">
                                <svg class="h-8 w-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h3 class="text-2xl font-black text-slate-800 mb-2">Conferma Azione</h3>
                            <p class="text-sm text-slate-500 mb-6">
                                Sei sicuro di voler <span x-text="userToToggle?.is_deleted ? 'abilitare' : 'disabilitare'"></span> l'utente <br>
                                <strong class="text-slate-800" x-text="userToToggle?.name"></strong>?
                            </p>
                        </div>
                        <div class="px-6 pb-6 bg-slate-50/50 pt-4 border-t border-slate-100 flex justify-center gap-3">
                            <button @click="closeConfirmModal()" class="flex-1 py-3 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 transition-colors shadow-sm">Annulla</button>
                            <button @click="executeToggle()" class="flex-1 py-3 bg-amber-500 text-white rounded-xl font-bold hover:bg-amber-600 transition-colors shadow-sm">Conferma</button>
                        </div>
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
                showConfirmModal: false, isClosingConfirm: false,
                showSuccessModal: false, isClosingSuccess: false,
                
                isEditing: false, isSubmitting: false,
                successMessage: '', successType: 'success',
                
                formData: { id: null, name: '', email: '', role: 'user', password: '' },
                userToToggle: null,

                get filteredUsers() {
                    if (!this.searchQuery) return this.users;
                    const q = this.searchQuery.toLowerCase();
                    return this.users.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q));
                },

                openCreateModal() { this.isEditing = false; this.formData = { id: null, name: '', email: '', role: 'user', password: '' }; this.showUserModal = true; },
                openEditModal(user) { this.isEditing = true; this.formData = { id: user.id, name: user.name, email: user.email, role: user.role, password: '' }; this.showUserModal = true; },
                closeUserModal() { this.isClosingModal = true; setTimeout(() => { this.showUserModal = false; this.isClosingModal = false; }, 300); },
                
                closeConfirmModal() { this.isClosingConfirm = true; setTimeout(() => { this.showConfirmModal = false; this.isClosingConfirm = false; this.userToToggle = null; }, 300); },
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
                },

                // Intercetta il click sullo switch e apre il modale di conferma
                toggleUserStatus(user) {
                    this.userToToggle = user;
                    this.showConfirmModal = true;
                    // Previene il cambio visivo dello switch fino alla conferma
                    event.preventDefault(); 
                },

                // Esegue l'API dopo aver confermato nel modale
                async executeToggle() {
                    if(!this.userToToggle) return;
                    try {
                        const response = await fetch(`/admin/users/${this.userToToggle.id}/toggle`, {
                            method: 'PATCH',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await response.json();
                        
                        this.closeConfirmModal();
                        
                        if (response.ok) {
                            await this.refreshData();
                            this.showSuccess(data.message, 'success');
                        } else {
                            this.showSuccess(data.message || "Errore sconosciuto", 'error');
                            await this.refreshData(); 
                        }
                    } catch (error) {
                        this.closeConfirmModal();
                        this.showSuccess('Errore di rete', 'error');
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

        /* Toggle Switch iOS */
        .switch { position: relative; display: inline-block; }
        .switch-sm { width: 44px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ef4444; transition: .4s; border-radius: 24px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        input:checked + .slider { background-color: #10b981; }
        input:checked + .slider:before { transform: translateX(20px); }

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
    </style>
</x-app-layout>