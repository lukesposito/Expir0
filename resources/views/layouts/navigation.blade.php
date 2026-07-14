<nav
    x-data="{
        open: false,
        scrolled: false,
        profileOpen: false,
        setScrolled() {
            this.scrolled = window.pageYOffset > 10;
        },
        lockScroll(state) {
            document.documentElement.classList.toggle('overflow-hidden', state);
            document.body.classList.toggle('overflow-hidden', state);
        },
        toggleMenu() {
            this.open = !this.open;
            this.lockScroll(this.open);
        },
        closeMenu() {
            this.open = false;
            this.lockScroll(false);
        }
    }"
    x-init="
        setScrolled();
        window.addEventListener('scroll', () => setScrolled());
        $watch('open', value => lockScroll(value));
    "
    @keydown.escape.window="closeMenu(); profileOpen = false"
    class="sticky top-0 z-50 w-full"
>
    <style>
        [x-cloak] { display: none !important; }

        .nav-shell {
            background:
                radial-gradient(circle at top left, rgba(34, 211, 238, 0.10), transparent 34%),
                radial-gradient(circle at top right, rgba(16, 185, 129, 0.10), transparent 36%),
                linear-gradient(135deg, rgba(255,255,255,0.94), rgba(255,255,255,0.78));
            border: 1px solid rgba(255,255,255,0.72);
            box-shadow:
                0 16px 46px -28px rgba(15,23,42,0.34),
                inset 0 1px 0 rgba(255,255,255,0.65);
        }

        .nav-shell-scrolled {
            background:
                radial-gradient(circle at top left, rgba(34, 211, 238, 0.12), transparent 34%),
                radial-gradient(circle at top right, rgba(16, 185, 129, 0.12), transparent 36%),
                linear-gradient(135deg, rgba(255,255,255,0.78), rgba(255,255,255,0.62));
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow:
                0 18px 54px -28px rgba(15,23,42,0.42),
                inset 0 1px 0 rgba(255,255,255,0.58);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
        }

        .nav-link-shine::before {
            content: "";
            position: absolute;
            inset: 1px;
            border-radius: 9999px;
            background: linear-gradient(135deg, rgba(255,255,255,0.18), rgba(255,255,255,0));
            opacity: 0;
            transition: opacity .28s ease;
            pointer-events: none;
        }

        .nav-link-shine:hover::before,
        .nav-link-shine:focus-visible::before {
            opacity: 1;
        }

        .nav-scroll-fade::before,
        .nav-scroll-fade::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            width: 2.8rem;
            z-index: 10;
            pointer-events: none;
        }

        .nav-scroll-fade::before {
            left: 0;
            background: linear-gradient(to right, rgba(248,250,252,1), rgba(248,250,252,0));
        }

        .nav-scroll-fade::after {
            right: 0;
            background: linear-gradient(to left, rgba(248,250,252,1), rgba(248,250,252,0));
        }

        .drawer-surface {
            background:
                radial-gradient(circle at top right, rgba(34,211,238,0.12), transparent 30%),
                radial-gradient(circle at top left, rgba(16,185,129,0.12), transparent 34%),
                linear-gradient(180deg, rgba(255,255,255,0.90), rgba(255,255,255,0.78));
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        .mobile-stagger {
            opacity: 0;
            transform: translateY(14px) scale(.985);
            animation: mobileEntry .48s cubic-bezier(.22,1,.36,1) forwards;
        }

        @keyframes mobileEntry {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .mobile-stagger {
                opacity: 1;
                transform: none;
                animation: none;
            }
        }
    </style>

    <div class="sticky top-0 z-50 px-3 pt-3 sm:px-4 lg:px-5">
        <div
            :class="scrolled ? 'nav-shell-scrolled' : 'nav-shell'"
            class="mb-4 w-full rounded-[1.6rem] transition-all duration-500 ease-out"
        >
            <div class="h-[3px] w-full rounded-t-[1.6rem] bg-gradient-to-r from-cyan-400 via-teal-400 to-emerald-500"></div>

            <div class="flex h-[4.35rem] items-center gap-3 px-3 sm:px-4 lg:px-5">
                {{-- Brand --}}
                <div class="flex min-w-0 flex-none items-center">
                    <a
                        href="{{ route('dashboard') }}"
                        class="group flex shrink-0 items-center rounded-2xl px-1 py-1.5 transition duration-300 hover:bg-white/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 focus-visible:ring-offset-2"
                    >
                        <div class="relative flex items-center justify-center px-1 py-1">
                            <x-application-logo class="relative h-16 w-auto object-contain transition-transform duration-300 group-hover:scale-105 motion-reduce:transform-none" />
                        </div>

                        <!-- <div class="ml-3 hidden min-w-0 sm:block">
                            <div class="truncate text-[0.68rem] font-semibold uppercase tracking-[0.28em] text-slate-400">
                                Smart panel
                            </div>
                            <div class="truncate text-sm font-semibold text-slate-900">
                                Le tue scadenze
                            </div>
                        </div> -->
                    </a>

                    {{-- Tablet / medium horizontal nav --}}
                    <div class="relative ml-5 hidden min-w-0 flex-1">
                        <div class="nav-scroll-fade relative">
                            <div class="flex items-center gap-2 overflow-x-auto py-1.5 pr-1 pl-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                <a
                                    href="{{ route('deadlines.index') }}"
                                    @if(request()->routeIs('deadlines.*')) aria-current="page" @endif
                                    class="{{ request()->routeIs('deadlines.*') ? 'bg-red-50 text-red-700 border border-red-200 shadow-sm' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950 border border-transparent' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                                >
                                    <svg viewBox="0 0 20 20" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3.5" y="4.5" width="13" height="12" rx="2.2"/>
                                        <path d="M3.5 8.3h13"/>
                                        <path d="M7 3v3M13 3v3"/>
                                    </svg>
                                    <span>Scadenze</span>
                                </a>

                                <a
                                    href="{{ route('vehicles.index') }}"
                                    @if(request()->routeIs('vehicles.*')) aria-current="page" @endif
                                    class="{{ request()->routeIs('vehicles.*') ? 'bg-slate-950 text-white shadow-[0_10px_24px_-12px_rgba(15,23,42,0.45)]' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                                >
                                    <svg viewBox="0 0 20 20" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4.5 12.5l1.3-4.1a2 2 0 011.9-1.4h4.6a2 2 0 011.9 1.4l1.3 4.1"/>
                                        <rect x="3" y="12.5" width="14" height="3.5" rx="1.2"/>
                                        <circle cx="6.3" cy="16.3" r="1.1"/>
                                        <circle cx="13.7" cy="16.3" r="1.1"/>
                                    </svg>
                                    <span>Veicoli</span>
                                </a>

                                <a
                                    href="{{ route('patenti.index') }}"
                                    @if(request()->routeIs('patenti.*')) aria-current="page" @endif
                                    class="{{ request()->routeIs('patenti.*') ? 'bg-slate-950 text-white shadow-[0_10px_24px_-12px_rgba(15,23,42,0.45)]' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                                >
                                    <svg viewBox="0 0 20 20" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="10" cy="10" r="6.7"/>
                                        <circle cx="10" cy="10" r="1.6"/>
                                        <path d="M10 3.3V6M10 14v2.7M3.3 10H6M14 10h2.7M5.5 5.5l1.9 1.9M12.6 12.6l1.9 1.9M5.5 14.5l1.9-1.9M12.6 7.4l1.9-1.9"/>
                                    </svg>
                                    <span>Patenti</span>
                                </a>

                                <a
                                    href="{{ route('revisioni.index') }}"
                                    @if(request()->routeIs('revisioni.*')) aria-current="page" @endif
                                    class="{{ request()->routeIs('revisioni.*') ? 'bg-slate-950 text-white shadow-[0_10px_24px_-12px_rgba(15,23,42,0.45)]' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                                >
                                    <svg viewBox="0 0 20 20" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12.6 4.3a3.3 3.3 0 00-4.4 3.9L4 12.4v2.3h2.3l4.2-4.2a3.3 3.3 0 003.9-4.4l-2 2-1.9-.6-.6-1.9z"/>
                                    </svg>
                                    <span>Revisioni</span>
                                </a>

                                <a
                                    href="{{ route('bolli.index') }}"
                                    @if(request()->routeIs('bolli.*')) aria-current="page" @endif
                                    class="{{ request()->routeIs('bolli.*') ? 'bg-slate-950 text-white shadow-[0_10px_24px_-12px_rgba(15,23,42,0.45)]' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                                >
                                    <svg viewBox="0 0 20 20" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10.6 3.5H5.8a1.2 1.2 0 00-1.2 1.2v4.8c0 .3.1.6.4.9l6.9 6.9a1.2 1.2 0 001.7 0l4.8-4.8a1.2 1.2 0 000-1.7l-6.9-6.9c-.2-.2-.5-.4-.9-.4z"/>
                                        <circle cx="8" cy="7.5" r="1"/>
                                    </svg>
                                    <span>Bolli</span>
                                </a>

                                <a
                                    href="{{ route('assicurazioni.index') }}"
                                    @if(request()->routeIs('assicurazioni.*')) aria-current="page" @endif
                                    class="{{ request()->routeIs('assicurazioni.*') ? 'bg-slate-950 text-white shadow-[0_10px_24px_-12px_rgba(15,23,42,0.45)]' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                                >
                                    <svg viewBox="0 0 20 20" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10 3.2l5.8 2.1v4.6c0 3.6-2.4 6.4-5.8 7.2-3.4-.8-5.8-3.6-5.8-7.2V5.3L10 3.2z"/>
                                        <path d="M7.4 10l1.8 1.8L12.7 8"/>
                                    </svg>
                                    <span>Assicurazioni</span>
                                </a>

                                <a
                                    href="{{ route('carte-identita.index') }}"
                                    @if(request()->routeIs('carte-identita.*')) aria-current="page" @endif
                                    class="{{ request()->routeIs('carte-identita.*') ? 'bg-slate-950 text-white shadow-[0_10px_24px_-12px_rgba(15,23,42,0.45)]' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                                >
                                    <svg viewBox="0 0 20 20" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4.5" width="14" height="11" rx="1.8"/>
                                        <circle cx="7" cy="9" r="1.6"/>
                                        <path d="M4.8 13.5c.4-1.4 1.3-2.1 2.2-2.1s1.8.7 2.2 2.1M12 8h4M12 11h4M12 14h2.5"/>
                                    </svg>
                                    <span>Carte d'Identità</span>
                                </a>

                                <a
                                    href="{{ route('tesserini-sanitari.index') }}"
                                    @if(request()->routeIs('tesserini-sanitari.*')) aria-current="page" @endif
                                    class="{{ request()->routeIs('tesserini-sanitari.*') ? 'bg-slate-950 text-white shadow-[0_10px_24px_-12px_rgba(15,23,42,0.45)]' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                                >
                                    <svg viewBox="0 0 20 20" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="10" cy="10" r="6.7"/>
                                        <path d="M10 6.8v6.4M6.8 10h6.4"/>
                                    </svg>
                                    <span>Tess. Sanitari</span>
                                </a>

                                <a
                                    href="{{ route('subscriptions.index') }}"
                                    @if(request()->routeIs('subscriptions.*')) aria-current="page" @endif
                                    class="{{ request()->routeIs('subscriptions.*') ? 'bg-violet-50 text-violet-700 border border-violet-200 shadow-sm' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950 border border-transparent' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                                >
                                    <svg class="h-4 w-4 shrink-0 opacity-85 transition duration-300 group-hover:opacity-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="5.5" width="14" height="10" rx="1.8"/>
                                        <path d="M3 8.5h14"/>
                                        <circle cx="14.2" cy="12" r="1"/>
                                    </svg>
                                    <span class="flex items-center gap-2">
                                        <span>Spese</span>
                                        <span class="rounded-full bg-emerald-500/12 px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-[0.18em] text-emerald-600">
                                            Sub
                                        </span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Desktop nav --}}
                    <div class="hidden lg:flex lg:items-center lg:ml-4 lg:gap-1.5">
                        <a
                            href="{{ route('deadlines.index') }}"
                            @if(request()->routeIs('deadlines.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('deadlines.*') ? 'bg-red-50 text-red-700 border border-red-200 shadow-sm' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950 border border-transparent' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                        >
                            <svg class="h-4 w-4 shrink-0 opacity-85 transition duration-300 group-hover:opacity-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3.5" y="4.5" width="13" height="12" rx="2.2"/>
                                <path d="M3.5 8.3h13"/>
                                <path d="M7 3v3M13 3v3"/>
                            </svg>
                            <span>Scadenze</span>
                            @if(request()->routeIs('deadlines.*'))
                                <span class="absolute inset-x-4 -bottom-0.5 h-[2px] rounded-full bg-white/75"></span>
                            @endif
                        </a>

                        <a
                            href="{{ route('vehicles.index') }}"
                            @if(request()->routeIs('vehicles.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('vehicles.*') ? 'bg-blue-50 text-blue-700 border border-blue-200 shadow-sm' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950 border border-transparent' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                        >
                            <svg class="h-4 w-4 shrink-0 opacity-85 transition duration-300 group-hover:opacity-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4.5 12.5l1.3-4.1a2 2 0 011.9-1.4h4.6a2 2 0 011.9 1.4l1.3 4.1"/>
                                <rect x="3" y="12.5" width="14" height="3.5" rx="1.2"/>
                                <circle cx="6.3" cy="16.3" r="1.1"/>
                                <circle cx="13.7" cy="16.3" r="1.1"/>
                            </svg>
                            <span>Veicoli</span>
                            @if(request()->routeIs('vehicles.*'))
                                <span class="absolute inset-x-4 -bottom-0.5 h-[2px] rounded-full bg-white/75"></span>
                            @endif
                        </a>

                        <a
                            href="{{ route('patenti.index') }}"
                            @if(request()->routeIs('patenti.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('patenti.*') ? 'bg-pink-50 text-pink-700 border border-pink-200 shadow-sm' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950 border border-transparent' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                        >
                            <svg class="h-4 w-4 shrink-0 opacity-85 transition duration-300 group-hover:opacity-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="10" cy="10" r="6.7"/>
                                <circle cx="10" cy="10" r="1.6"/>
                                <path d="M10 3.3V6M10 14v2.7M3.3 10H6M14 10h2.7M5.5 5.5l1.9 1.9M12.6 12.6l1.9 1.9M5.5 14.5l1.9-1.9M12.6 7.4l1.9-1.9"/>
                            </svg>
                            <span>Patenti</span>
                            @if(request()->routeIs('patenti.*'))
                                <span class="absolute inset-x-4 -bottom-0.5 h-[2px] rounded-full bg-white/75"></span>
                            @endif
                        </a>

                        <a
                            href="{{ route('revisioni.index') }}"
                            @if(request()->routeIs('revisioni.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('revisioni.*') ? 'bg-green-50 text-green-700 border border-green-200 shadow-sm' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950 border border-transparent' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                        >
                            <svg class="h-4 w-4 shrink-0 opacity-85 transition duration-300 group-hover:opacity-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.6 4.3a3.3 3.3 0 00-4.4 3.9L4 12.4v2.3h2.3l4.2-4.2a3.3 3.3 0 003.9-4.4l-2 2-1.9-.6-.6-1.9z"/>
                            </svg>
                            <span>Revisioni</span>
                            @if(request()->routeIs('revisioni.*'))
                                <span class="absolute inset-x-4 -bottom-0.5 h-[2px] rounded-full bg-white/75"></span>
                            @endif
                        </a>

                        <a
                            href="{{ route('bolli.index') }}"
                            @if(request()->routeIs('bolli.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('bolli.*') ? 'bg-yellow-50 text-yellow-700 border border-yellow-200 shadow-sm' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950 border border-transparent' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                        >
                            <svg class="h-4 w-4 shrink-0 opacity-85 transition duration-300 group-hover:opacity-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.6 3.5H5.8a1.2 1.2 0 00-1.2 1.2v4.8c0 .3.1.6.4.9l6.9 6.9a1.2 1.2 0 001.7 0l4.8-4.8a1.2 1.2 0 000-1.7l-6.9-6.9c-.2-.2-.5-.4-.9-.4z"/>
                                <circle cx="8" cy="7.5" r="1"/>
                            </svg>
                            <span>Bolli</span>
                            @if(request()->routeIs('bolli.*'))
                                <span class="absolute inset-x-4 -bottom-0.5 h-[2px] rounded-full bg-white/75"></span>
                            @endif
                        </a>

                        <a
                            href="{{ route('assicurazioni.index') }}"
                            @if(request()->routeIs('assicurazioni.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('assicurazioni.*') ? 'bg-orange-50 text-orange-700 border border-orange-200 shadow-sm' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950 border border-transparent' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                        >
                            <svg class="h-4 w-4 shrink-0 opacity-85 transition duration-300 group-hover:opacity-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 3.2l5.8 2.1v4.6c0 3.6-2.4 6.4-5.8 7.2-3.4-.8-5.8-3.6-5.8-7.2V5.3L10 3.2z"/>
                                <path d="M7.4 10l1.8 1.8L12.7 8"/>
                            </svg>
                            <span>Assicurazioni</span>
                            @if(request()->routeIs('assicurazioni.*'))
                                <span class="absolute inset-x-4 -bottom-0.5 h-[2px] rounded-full bg-white/75"></span>
                            @endif
                        </a>

                        <a
                            href="{{ route('carte-identita.index') }}"
                            @if(request()->routeIs('carte-identita.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('carte-identita.*') ? 'bg-amber-50 text-amber-700 border border-amber-200 shadow-sm' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950 border border-transparent' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                        >
                            <svg class="h-4 w-4 shrink-0 opacity-85 transition duration-300 group-hover:opacity-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4.5" width="14" height="11" rx="1.8"/>
                                <circle cx="7" cy="9" r="1.6"/>
                                <path d="M4.8 13.5c.4-1.4 1.3-2.1 2.2-2.1s1.8.7 2.2 2.1M12 8h4M12 11h4M12 14h2.5"/>
                            </svg>
                            <span>Carte d'Identità</span>
                            @if(request()->routeIs('carte-identita.*'))
                                <span class="absolute inset-x-4 -bottom-0.5 h-[2px] rounded-full bg-white/75"></span>
                            @endif
                        </a>

                        <a
                            href="{{ route('tesserini-sanitari.index') }}"
                            @if(request()->routeIs('tesserini-sanitari.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('tesserini-sanitari.*') ? 'bg-cyan-50 text-cyan-700 border border-cyan-200 shadow-sm' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950 border border-transparent' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                        >
                            <svg class="h-4 w-4 shrink-0 opacity-85 transition duration-300 group-hover:opacity-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="10" cy="10" r="6.7"/>
                                <path d="M10 6.8v6.4M6.8 10h6.4"/>
                            </svg>
                            <span>Tess. Sanitari</span>
                            @if(request()->routeIs('tesserini-sanitari.*'))
                                <span class="absolute inset-x-4 -bottom-0.5 h-[2px] rounded-full bg-white/75"></span>
                            @endif
                        </a>

                        <a
                            href="{{ route('subscriptions.index') }}"
                            @if(request()->routeIs('subscriptions.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('subscriptions.*') ? 'bg-violet-50 text-violet-700 border border-violet-200 shadow-sm' : 'bg-white/55 text-slate-600 hover:bg-white/85 hover:text-slate-950 border border-transparent' }} nav-link-shine relative inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition duration-300 hover:-translate-y-[1px]"
                        >
                            <svg class="h-4 w-4 shrink-0 opacity-85 transition duration-300 group-hover:opacity-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5.5" width="14" height="10" rx="1.8"/>
                                <path d="M3 8.5h14"/>
                                <circle cx="14.2" cy="12" r="1"/>
                            </svg>
                            <span class="flex items-center gap-2">
                                <span>Spese & Abbon.</span>
                                @if(request()->routeIs('subscriptions.*'))
                                    <span class="absolute inset-x-4 -bottom-0.5 h-[2px] rounded-full bg-white/75"></span>
                                @endif
                            </span>
                        </a>
                    </div>
                </div>

                {{-- Desktop account --}}
                <div class="ml-auto hidden shrink-0 sm:flex sm:items-center sm:mr-3 -translate-x-1 relative z-[70]" @click.outside="profileOpen = false">
                    <div class="relative">
                        <button
                            type="button"
                            @click="profileOpen = !profileOpen"
                            :aria-expanded="profileOpen.toString()"
                            class="group inline-flex items-center gap-3 rounded-2xl border border-white/65 bg-white/70 px-2.5 py-2 text-left shadow-[0_10px_24px_-18px_rgba(15,23,42,0.26)] backdrop-blur-xl transition duration-300 hover:-translate-y-[1px] hover:bg-white/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50"class="group inline-flex items-center gap-3 rounded-2xl border border-white/65 bg-white/70 px-2.5 py-2 text-left shadow-[0_10px_24px_-18px_rgba(15,23,42,0.26)] backdrop-blur-xl transition duration-300 hover:-translate-y-[1px] hover:bg-white/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 translate-x-1"
                        >
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-emerald-400 text-sm font-bold uppercase text-white shadow-[0_10px_20px_-10px_rgba(6,182,212,0.45)]">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>

                            <div class="hidden max-w-[9rem] xl:block">
                                <div class="truncate text-xs font-medium uppercase tracking-[0.2em] text-slate-400">
                                    Account
                                </div>
                                <div class="truncate text-sm font-semibold text-slate-900">
                                    {{ Auth::user()->name }}
                                </div>
                            </div>

                            <svg
                                class="h-4 w-4 text-slate-500 transition duration-300"
                                :class="profileOpen ? 'rotate-180' : ''"
                                viewBox="0 0 20 20"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path d="m5 7.5 5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <div
                            x-cloak
                            x-show="profileOpen"
                            x-transition:enter="transition ease-[cubic-bezier(.22,1,.36,1)] duration-300"
                            x-transition:enter-start="opacity-0 translate-y-2 scale-[.98]"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-2 scale-[.98]"
                            class="absolute right-0 top-full z-[80] mt-3 w-72 overflow-hidden rounded-[1.6rem] border border-white/70 bg-white/90 p-2 shadow-[0_24px_60px_-26px_rgba(15,23,42,0.42)] backdrop-blur-2xl"
                        >
                            <div class="rounded-[1.2rem] border border-cyan-200/70 bg-gradient-to-br from-cyan-50 via-white to-emerald-50 px-4 py-4 text-slate-800 shadow-[0_12px_24px_-18px_rgba(6,182,212,0.30)]">
                                <div class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-cyan-600/70">
                                    Profilo
                                </div>

                                <div class="mt-3 flex items-center gap-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-emerald-400 text-sm font-bold uppercase text-white shadow-[0_10px_18px_-10px_rgba(6,182,212,0.55)]">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>

                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                                        <div class="truncate text-sm text-cyan-700/80">{{ Auth::user()->email }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2 space-y-1">
                                <a
                                    href="{{ route('profile.edit') }}"
                                    class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition duration-200 hover:bg-cyan-50 hover:text-cyan-700"
                                >
                                    <span>{{ __('Profilo') }}</span>
                                    <span class="text-slate-400 transition duration-200 hover:text-cyan-500">↗</span>
                                </a>

                                @if(Auth::user()->role === 'admin')
                                    <a
                                        href="{{ route('admin.users.index') }}"
                                        class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition duration-200 hover:bg-cyan-50 hover:text-cyan-700"
                                    >
                                        <span>{{ __('Gestione Utenti') }}</span>
                                        <span class="text-slate-400 transition duration-200 hover:text-cyan-500">↗</span>
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="flex w-full items-center justify-between rounded-2xl px-4 py-3 text-sm font-semibold text-rose-600 transition duration-200 hover:bg-rose-50 hover:text-rose-700"
                                    >
                                        <span>{{ __('Disconnetti') }}</span>
                                        <span>⤴</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mobile hamburger --}}
                <div class="ml-auto flex shrink-0 items-center lg:hidden">
                    <button
                        type="button"
                        @click="toggleMenu()"
                        :aria-expanded="open.toString()"
                        aria-label="Apri menu principale"
                        class="relative inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-white/65 bg-white/60 text-slate-900 shadow-[0_10px_24px_-18px_rgba(15,23,42,0.32)] backdrop-blur-xl transition duration-300 hover:scale-[1.03] hover:bg-white/85 active:scale-95"
                    >
                        <span
                            class="absolute transition-all duration-300 ease-out"
                            :class="open ? 'scale-0 rotate-180 opacity-0' : 'scale-100 rotate-0 opacity-100'"
                        >
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                                <path d="M4 7h16M7 12h13M4 17h16" stroke-linecap="round" />
                            </svg>
                        </span>

                        <span
                            class="absolute transition-all duration-300 ease-out"
                            :class="open ? 'scale-100 rotate-0 opacity-100' : 'scale-0 -rotate-180 opacity-0'"
                        >
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                                <path d="M6 6 18 18M18 6 6 18" stroke-linecap="round" />
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile overlay --}}
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition-opacity duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeMenu()"
        class="fixed inset-0 z-40 bg-slate-950/45 backdrop-blur-md lg:hidden"
    ></div>

    {{-- Mobile drawer --}}
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition duration-500 ease-[cubic-bezier(.22,1,.36,1)]"
        x-transition:enter-start="opacity-0 translate-y-6 scale-[.985]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition duration-250 ease-in"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-5 scale-[.985]"
        class="fixed inset-x-3 top-[5.55rem] z-50 max-h-[calc(100dvh-6.4rem)] overflow-hidden rounded-[2rem] border border-white/70 shadow-[0_32px_90px_-30px_rgba(15,23,42,0.50)] lg:hidden"
    >
        <div class="drawer-surface max-h-[calc(100dvh-6.4rem)] overflow-y-auto p-3">
            <div class="mb-3 flex items-center justify-between rounded-[1.5rem] bg-slate-950 px-4 py-4 text-white shadow-[0_16px_40px_-24px_rgba(15,23,42,0.55)]">
                <div class="min-w-0">
                    <div class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-white/45">
                        Navigazione
                    </div>
                    <div class="truncate text-base font-semibold">Muoviti veloce nel pannello</div>
                </div>

                <button
                    type="button"
                    @click="closeMenu()"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-white transition duration-200 hover:bg-white/15 active:scale-95"
                    aria-label="Chiudi menu"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path d="M6 6 18 18M18 6 6 18" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <div class="space-y-2">
                <a
                    href="{{ route('deadlines.index') }}"
                    @click="closeMenu()"
                    class="{{ request()->routeIs('deadlines.*') ? 'bg-red-50 text-red-700 border border-red-200 shadow-sm' : 'text-slate-600 hover:bg-white/80 hover:text-slate-950 border border-transparent' }} mobile-stagger flex items-center justify-between rounded-[1.45rem] px-4 py-4 transition duration-300 hover:-translate-y-[1px]"
                    style="animation-delay: 70ms;"
                >
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ request()->routeIs('deadlines.*') ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-700' }}">
                            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3.5" y="4.5" width="13" height="12" rx="2.2"/>
                                <path d="M3.5 8.3h13"/>
                                <path d="M7 3v3M13 3v3"/>
                            </svg>
                        </span>

                        <span class="min-w-0">
                            <span class="block truncate text-[1rem] font-semibold tracking-[-0.01em]">Scadenze</span>
                            <span class="block text-xs uppercase tracking-[0.22em] {{ request()->routeIs('deadlines.*') ? 'text-red-400' : 'text-slate-400' }}">Area</span>
                        </span>
                    </span>

                    <span class="ml-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ request()->routeIs('deadlines.*') ? 'bg-red-100 text-red-600' : 'bg-white text-slate-400' }}">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M7 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </a>
                <a
                    href="{{ route('vehicles.index') }}"
                    @click="closeMenu()"
                    class="{{ request()->routeIs('vehicles.*') ? 'bg-blue-50 text-blue-700 border border-blue-200 shadow-sm' : 'text-slate-600 hover:bg-white/80 hover:text-slate-950 border border-transparent' }} mobile-stagger flex items-center justify-between rounded-[1.45rem] px-4 py-4 transition duration-300 hover:-translate-y-[1px]"
                    style="animation-delay: 115ms;"
                >
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ request()->routeIs('vehicles.*') ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-700' }}">
                            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4.5 12.5l1.3-4.1a2 2 0 011.9-1.4h4.6a2 2 0 011.9 1.4l1.3 4.1"/>
                                <rect x="3" y="12.5" width="14" height="3.5" rx="1.2"/>
                                <circle cx="6.3" cy="16.3" r="1.1"/>
                                <circle cx="13.7" cy="16.3" r="1.1"/>
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-[1rem] font-semibold tracking-[-0.01em]">Veicoli</span>
                            <span class="block text-xs uppercase tracking-[0.22em] {{ request()->routeIs('vehicles.*') ? 'text-blue-400' : 'text-slate-400' }}">Area</span>
                        </span>
                    </span>
                    <span class="ml-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ request()->routeIs('vehicles.*') ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-700' }}">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M7 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </a>

                <a
                    href="{{ route('patenti.index') }}"
                    @click="closeMenu()"
                    class="{{ request()->routeIs('patenti.*') ? 'bg-pink-50 text-pink-700 border border-pink-200 shadow-sm' : 'text-slate-600 hover:bg-white/80 hover:text-slate-950 border border-transparent' }} mobile-stagger flex items-center justify-between rounded-[1.45rem] px-4 py-4 transition duration-300 hover:-translate-y-[1px]"
                    style="animation-delay: 160ms;"
                >
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ request()->routeIs('patenti.*') ? 'bg-pink-100 text-pink-600' : 'bg-slate-100 text-slate-700' }}">
                            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="10" cy="10" r="6.7"/>
                                <circle cx="10" cy="10" r="1.6"/>
                                <path d="M10 3.3V6M10 14v2.7M3.3 10H6M14 10h2.7M5.5 5.5l1.9 1.9M12.6 12.6l1.9 1.9M5.5 14.5l1.9-1.9M12.6 7.4l1.9-1.9"/>
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-[1rem] font-semibold tracking-[-0.01em]">Patenti</span>
                            <span class="block text-xs uppercase tracking-[0.22em] {{ request()->routeIs('patenti.*') ? 'text-pink-400' : 'text-slate-400' }}">Area</span>
                        </span>
                    </span>
                    <span class="ml-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ request()->routeIs('patenti.*') ? 'bg-pink-100 text-pink-600' : 'bg-white text-slate-400' }}">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M7 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </a>

                <a
                    href="{{ route('revisioni.index') }}"
                    @click="closeMenu()"
                    class="{{ request()->routeIs('revisioni.*') ? 'bg-green-50 text-green-700 border border-green-200 shadow-sm' : 'text-slate-600 hover:bg-white/80 hover:text-slate-950 border border-transparent' }} mobile-stagger flex items-center justify-between rounded-[1.45rem] px-4 py-4 transition duration-300 hover:-translate-y-[1px]"
                    style="animation-delay: 205ms;"
                >
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ request()->routeIs('revisioni.*') ? 'bg-green-100 text-green-600' : 'bg-slate-100 text-slate-700' }}">
                            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.6 4.3a3.3 3.3 0 00-4.4 3.9L4 12.4v2.3h2.3l4.2-4.2a3.3 3.3 0 003.9-4.4l-2 2-1.9-.6-.6-1.9z"/>
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-[1rem] font-semibold tracking-[-0.01em]">Revisioni</span>
                            <span class="block text-xs uppercase tracking-[0.22em] {{ request()->routeIs('revisioni.*') ? 'text-green-400' : 'text-slate-400' }}">Area</span>
                        </span>
                    </span>
                    <span class="ml-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ request()->routeIs('revisioni.*') ? 'bg-green-100 text-green-600' : 'bg-white text-slate-400' }}">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M7 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </a>

                <a
                    href="{{ route('bolli.index') }}"
                    @click="closeMenu()"
                    class="{{ request()->routeIs('bolli.*') ? 'bg-yellow-50 text-yellow-700 border border-yellow-200 shadow-sm' : 'text-slate-600 hover:bg-white/80 hover:text-slate-950 border border-transparent' }} mobile-stagger flex items-center justify-between rounded-[1.45rem] px-4 py-4 transition duration-300 hover:-translate-y-[1px]"
                    style="animation-delay: 250ms;"
                >
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ request()->routeIs('bolli.*') ? 'bg-yellow-100 text-yellow-600' : 'bg-slate-100 text-slate-700' }}">
                            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.6 3.5H5.8a1.2 1.2 0 00-1.2 1.2v4.8c0 .3.1.6.4.9l6.9 6.9a1.2 1.2 0 001.7 0l4.8-4.8a1.2 1.2 0 000-1.7l-6.9-6.9c-.2-.2-.5-.4-.9-.4z"/>
                                <circle cx="8" cy="7.5" r="1"/>
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-[1rem] font-semibold tracking-[-0.01em]">Bolli</span>
                            <span class="block text-xs uppercase tracking-[0.22em] {{ request()->routeIs('bolli.*') ? 'text-yellow-400' : 'text-slate-400' }}">Area</span>
                        </span>
                    </span>
                    <span class="ml-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ request()->routeIs('bolli.*') ? 'bg-yellow-100 text-yellow-600' : 'bg-white text-slate-400' }}">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M7 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </a>

                <a
                    href="{{ route('assicurazioni.index') }}"
                    @click="closeMenu()"
                    class="{{ request()->routeIs('assicurazioni.*') ? 'bg-orange-50 text-orange-700 border border-orange-200 shadow-sm' : 'text-slate-600 hover:bg-white/80 hover:text-slate-950 border border-transparent' }} mobile-stagger flex items-center justify-between rounded-[1.45rem] px-4 py-4 transition duration-300 hover:-translate-y-[1px]"
                    style="animation-delay: 295ms;"
                >
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ request()->routeIs('assicurazioni.*') ? 'bg-orange-100 text-orange-600' : 'bg-slate-100 text-slate-700' }}">
                            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 3.2l5.8 2.1v4.6c0 3.6-2.4 6.4-5.8 7.2-3.4-.8-5.8-3.6-5.8-7.2V5.3L10 3.2z"/>
                                <path d="M7.4 10l1.8 1.8L12.7 8"/>
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-[1rem] font-semibold tracking-[-0.01em]">Assicurazioni</span>
                            <span class="block text-xs uppercase tracking-[0.22em] {{ request()->routeIs('assicurazioni.*') ? 'text-orange-400' : 'text-slate-400' }}">Area</span>
                        </span>
                    </span>
                    <span class="ml-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ request()->routeIs('assicurazioni.*') ? 'bg-orange-100 text-orange-600' : 'bg-white text-slate-400' }}">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M7 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </a>

                <a
                    href="{{ route('carte-identita.index') }}"
                    @click="closeMenu()"
                    class="{{ request()->routeIs('carte-identita.*') ? 'bg-amber-50 text-amber-700 border border-amber-200 shadow-sm' : 'text-slate-600 hover:bg-white/80 hover:text-slate-950 border border-transparent' }} mobile-stagger flex items-center justify-between rounded-[1.45rem] px-4 py-4 transition duration-300 hover:-translate-y-[1px]"
                    style="animation-delay: 340ms;"
                >
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ request()->routeIs('carte-identita.*') ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-700' }}">
                            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4.5" width="14" height="11" rx="1.8"/>
                                <circle cx="7" cy="9" r="1.6"/>
                                <path d="M4.8 13.5c.4-1.4 1.3-2.1 2.2-2.1s1.8.7 2.2 2.1M12 8h4M12 11h4M12 14h2.5"/>
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-[1rem] font-semibold tracking-[-0.01em]">Carte d'Identità</span>
                            <span class="block text-xs uppercase tracking-[0.22em] {{ request()->routeIs('carte-identita.*') ? 'text-amber-400' : 'text-slate-400' }}">Area</span>
                        </span>
                    </span>
                    <span class="ml-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ request()->routeIs('carte-identita.*') ? 'bg-amber-100 text-amber-600' : 'bg-white text-slate-400' }}">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M7 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </a>

                <a
                    href="{{ route('tesserini-sanitari.index') }}"
                    @click="closeMenu()"
                    class="{{ request()->routeIs('tesserini-sanitari.*') ? 'bg-cyan-50 text-cyan-700 border border-cyan-200 shadow-sm' : 'text-slate-600 hover:bg-white/80 hover:text-slate-950 border border-transparent' }} mobile-stagger flex items-center justify-between rounded-[1.45rem] px-4 py-4 transition duration-300 hover:-translate-y-[1px]"
                    style="animation-delay: 385ms;"
                >
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ request()->routeIs('tesserini-sanitari.*') ? 'bg-cyan-100 text-cyan-600' : 'bg-slate-100 text-slate-700' }}">
                            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="10" cy="10" r="6.7"/>
                                <path d="M10 6.8v6.4M6.8 10h6.4"/>
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-[1rem] font-semibold tracking-[-0.01em]">Tesserini Sanitari</span>
                            <span class="block text-xs uppercase tracking-[0.22em] {{ request()->routeIs('tesserini-sanitari.*') ? 'text-cyan-400' : 'text-slate-400' }}">Area</span>
                        </span>
                    </span>
                    <span class="ml-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ request()->routeIs('tesserini-sanitari.*') ? 'bg-cyan-100 text-cyan-600' : 'bg-white text-slate-400' }}">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M7 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </a>

                <a
                    href="{{ route('subscriptions.index') }}"
                    @click="closeMenu()"
                    class="{{ request()->routeIs('subscriptions.*') ? 'bg-violet-50 text-violet-700 border border-violet-200 shadow-sm' : 'text-slate-600 hover:bg-white/80 hover:text-slate-950 border border-transparent' }} mobile-stagger flex items-center justify-between rounded-[1.45rem] px-4 py-4 transition duration-300 hover:-translate-y-[1px]"
                    style="animation-delay: 430ms;"
                >
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ request()->routeIs('subscriptions.*') ? 'bg-violet-100 text-violet-600' : 'bg-slate-100 text-slate-700' }}">
                            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5.5" width="14" height="10" rx="1.8"/>
                                <path d="M3 8.5h14"/>
                                <circle cx="14.2" cy="12" r="1"/>
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-[1rem] font-semibold tracking-[-0.01em]">Spese e Abbonamenti</span>
                            <span class="block text-xs uppercase tracking-[0.22em] {{ request()->routeIs('subscriptions.*') ? 'text-violet-400' : 'text-slate-400' }}">Area</span>
                        </span>
                    </span>
                    <span class="ml-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ request()->routeIs('subscriptions.*') ? 'bg-violet-100 text-violet-600' : 'bg-white text-slate-400' }}">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M7 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </a>
            </div>

            <div class="mt-3">
                <div class="mobile-stagger rounded-[1.6rem] bg-white/58 p-3 shadow-[0_14px_28px_-24px_rgba(15,23,42,0.32)]" style="animation-delay: 520ms;">
                    <div class="rounded-[1.35rem] border border-cyan-200/70 bg-gradient-to-br from-cyan-50 via-white to-emerald-50 px-4 py-4 text-slate-800 shadow-[0_10px_24px_-18px_rgba(6,182,212,0.35)]">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-emerald-400 text-sm font-bold uppercase text-white shadow-[0_10px_18px_-10px_rgba(6,182,212,0.55)]">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>

                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                                <div class="truncate text-sm text-cyan-700/80">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 grid gap-2">
                        <a
                            href="{{ route('profile.edit') }}"
                            @click="closeMenu()"
                            class="flex items-center justify-between rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition duration-200 hover:bg-slate-50"
                        >
                            <span>{{ __('Profilo') }}</span>
                            <span class="text-slate-400">↗</span>
                        </a>

                        @if(Auth::user()->role === 'admin')
                            <a
                                href="{{ route('admin.users.index') }}"
                                @click="closeMenu()"
                                class="flex items-center justify-between rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition duration-200 hover:bg-slate-50"
                            >
                                <span>{{ __('Gestione Utenti') }}</span>
                                <span class="text-slate-400">↗</span>
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="flex w-full items-center justify-between rounded-2xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-600 transition duration-200 hover:bg-rose-100"
                            >
                                <span>{{ __('Disconnetti') }}</span>
                                <span>⤴</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>