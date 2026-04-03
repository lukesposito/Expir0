<nav x-data="{ open: false, scrolled: false }" 
     @scroll.window="scrolled = (window.pageYOffset > 20)" 
     :class="{ 'bg-white/85 backdrop-blur-md shadow-sm border-transparent': scrolled, 'bg-white border-gray-100': !scrolled }"
     class="sticky top-0 z-50 border-b transition-all duration-300">
     
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="shrink-0 flex items-center transform transition duration-300 hover:scale-105">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-indigo-600 drop-shadow-sm" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('deadlines.index')" :active="request()->routeIs('deadlines.*')">
                        {{ __('Scadenze') }}
                    </x-nav-link>
                    <x-nav-link :href="route('vehicles.index')" :active="request()->routeIs('vehicles.*')">
                        {{ __('Veicoli') }}
                    </x-nav-link>
                    <x-nav-link :href="route('patenti.index')" :active="request()->routeIs('patenti.*')">
                        {{ __('Patenti') }}
                    </x-nav-link>
                    <x-nav-link :href="route('revisioni.index')" :active="request()->routeIs('revisioni.*')">
                        {{ __('Revisioni') }}
                    </x-nav-link>
                    <x-nav-link :href="route('bolli.index')" :active="request()->routeIs('bolli.*')">
                        {{ __('Bolli') }}
                    </x-nav-link>
                    <x-nav-link :href="route('assicurazioni.index')" :active="request()->routeIs('assicurazioni.*')">
                        {{ __('Assicurazioni') }}
                    </x-nav-link>
                    <x-nav-link :href="route('carte-identita.index')" :active="request()->routeIs('carte-identita.*')">
                        {{ __('Carte d\'Identità') }}
                    </x-nav-link>
                    <x-nav-link :href="route('tesserini-sanitari.index')" :active="request()->routeIs('tesserini-sanitari.*')">
                        {{ __('Tess. Sanitari') }}
                    </x-nav-link>
                    
                    <x-nav-link :href="route('subscriptions.index')" :active="request()->routeIs('subscriptions.*')">
                        {{ __('Spese / Abbon.') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 pl-1 pr-3 py-1 border border-slate-200 rounded-full text-sm leading-4 font-bold text-slate-700 bg-white hover:text-indigo-600 hover:border-indigo-200 hover:shadow-sm focus:outline-none transition ease-in-out duration-200">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-black shadow-inner">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="fill-current h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="font-medium text-slate-600 hover:text-indigo-600">
                            {{ __('Profilo') }}
                        </x-dropdown-link>

                        @if(Auth::user()->role === 'admin')
                            <x-dropdown-link :href="route('admin.users.index')">
                                {{ __('Gestione Utenti') }}
                            </x-dropdown-link>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="font-medium text-red-500 hover:text-red-700">
                                {{ __('Disconnetti') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="relative group inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 focus:outline-none transition-all duration-300">
                    <span class="sr-only">Apri menu principale</span>
                    <div class="w-5 h-4 flex flex-col justify-between items-center overflow-hidden">
                        <span class="w-full h-[2px] bg-current transform transition-all duration-300 origin-left" :class="{'rotate-45 translate-x-px': open}"></span>
                        <span class="w-full h-[2px] bg-current transition-all duration-300" :class="{'opacity-0 translate-x-4': open}"></span>
                        <span class="w-full h-[2px] bg-current transform transition-all duration-300 origin-left" :class="{'-rotate-45 translate-x-px': open}"></span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-300 transform origin-top"
         x-transition:enter-start="opacity-0 -translate-y-4 scale-y-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
         x-transition:leave="transition ease-in duration-200 transform origin-top"
         x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
         x-transition:leave-end="opacity-0 -translate-y-4 scale-y-95"
         class="sm:hidden absolute top-full left-0 w-full bg-white/95 backdrop-blur-xl border-b border-slate-200 shadow-2xl">
        
        <div class="pt-2 pb-3 space-y-1 px-2">
            <x-responsive-nav-link :href="route('deadlines.index')" :active="request()->routeIs('deadlines.*')" class="rounded-lg">
                Scadenze
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('vehicles.index')" :active="request()->routeIs('vehicles.*')" class="rounded-lg">
                Veicoli
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('patenti.index')" :active="request()->routeIs('patenti.*')" class="rounded-lg">
                Patenti
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('revisioni.index')" :active="request()->routeIs('revisioni.*')" class="rounded-lg">
                Revisioni
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('bolli.index')" :active="request()->routeIs('bolli.*')" class="rounded-lg">
                Bolli
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('assicurazioni.index')" :active="request()->routeIs('assicurazioni.*')" class="rounded-lg">
                Assicurazioni
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('carte-identita.index')" :active="request()->routeIs('carte-identita.*')" class="rounded-lg">
                Carte d'Identità
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('tesserini-sanitari.index')" :active="request()->routeIs('tesserini-sanitari.*')" class="rounded-lg">
                Tesserini Sanitari
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('subscriptions.index')" :active="request()->routeIs('subscriptions.*')" class="rounded-lg">
                Spese e Abbonamenti
            </x-responsive-nav-link>
            
        </div>

        <div class="pt-4 pb-4 border-t border-slate-100 bg-slate-50/50">
            <div class="px-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-black shadow-md">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-bold text-base text-slate-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-4 space-y-1 px-2">
                <x-responsive-nav-link :href="route('profile.edit')" class="rounded-lg text-slate-600 font-medium">
                    {{ __('Profilo') }}
                </x-responsive-nav-link>

                @if(Auth::user()->role === 'admin')
                    <x-responsive-nav-link :href="route('admin.users.index')" class="rounded-lg text-slate-600 font-medium">
                        {{ __('Gestione Utenti') }}
                    </x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();"
                            class="rounded-lg text-red-500 font-bold hover:text-red-600 hover:bg-red-50">
                        {{ __('Disconnetti') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</nav>