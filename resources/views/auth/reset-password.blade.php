@vite(['resources/css/app.css', 'resources/js/app.js'])
@php
    $showWeakPasswordModal = $errors->has('password');
@endphp
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Reimposta Password Expir0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Nunito', system-ui, -apple-system, sans-serif; }
        html, body { min-height: 100vh; }
        body { display: flex; justify-content: center; align-items: center; overflow-x: hidden; opacity: 0; transition: opacity 0.35s ease, transform 0.35s ease; }
        body.page-ready { opacity: 1; }
        body.page-leaving { opacity: 0; transform: scale(0.985); }
        .overlay { position: fixed; inset: 0; background: radial-gradient(circle at 15% 15%, rgba(0, 201, 246, 0.65), transparent 55%), radial-gradient(circle at 85% 85%, rgba(37, 99, 235, 0.65), transparent 55%); backdrop-filter: blur(2px); }
        .login-wrapper { position: relative; z-index: 1; width: 100%; max-width: 420px; padding: 24px; }
        .login-card { position: relative; width: 100%; background: linear-gradient(180deg, #ffffff 0%, #f3f4f6 100%); border-radius: 24px; box-shadow: 0 24px 60px rgba(15, 23, 42, 0.45); padding: 64px 40px 32px 40px; text-align: center; }
        .logo-badge { position: absolute; top: -76px; left: 50%; transform: translateX(-50%); width: 152px; height: 144px; border-radius: 50%; background: radial-gradient(circle at 20% 0%, #00ddff, #0cfd99); box-shadow: 0 14px 35px rgba(15, 23, 42, 0.45); display: flex; align-items: center; justify-content: center; padding: 10px; }
        .logo-badge-inner { width: 100%; height: 100%; border-radius: 50%; background: #ffffff; border: 4px solid #1e3a8a; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .logo-badge-inner img { width: 96%; height: 96%; object-fit: contain; object-position: center; mask-image: radial-gradient(circle 85% at center, white 60%, transparent 70%); -webkit-mask-image: radial-gradient(circle 85% at center, white 60%, transparent 70%); }
        .title { margin-top: 16px; font-size: 20px; font-weight: 700; color: #0f172a; }
        .subtitle { margin-top: 6px; font-size: 13px; font-weight: 400; color: #6b7280; line-height: 1.5; }
        form { margin-top: 24px; text-align: left; }
        .form-group { margin-bottom: 16px; position: relative; }
        .form-label { font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 4px; display: block; text-transform: uppercase; letter-spacing: 0.04em; }
        .form-control-wrap { position: relative; display: flex; align-items: center; border-radius: 999px; padding: 0 14px; background: linear-gradient(90deg, #e5e7eb, #f9fafb); box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.7); transition: all 0.3s ease; }
        .form-control-wrap:focus-within { box-shadow: inset 0 0 0 2px #2563eb; background: #ffffff; }
        .form-control-wrap input { flex: 1; border: none; background: transparent; padding: 10px 0; font-size: 14px; color: #111827; outline: none; width: 100%; }
        .form-control-wrap input::placeholder { color: #9ca3af; }
        .icon-left { color: #2563eb; margin-right: 8px; display: flex; align-items: center; justify-content: center; }
        .btn-submit { margin-top: 18px; width: 100%; border: none; border-radius: 999px; padding: 11px 18px; font-size: 14px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #ffffff; background: linear-gradient(90deg, #00ddff, #0cfd99); box-shadow: 0 12px 30px rgba(37, 99, 235, 0.65); cursor: pointer; transition: all 0.2s ease-out; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .btn-submit:hover { filter: brightness(1.05); box-shadow: 0 18px 40px rgba(37, 99, 235, 0.8); }
        .btn-submit:active { transform: translateY(1px); box-shadow: 0 10px 24px rgba(37, 99, 235, 0.7); }
        .secondary-link { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #6b7280; text-decoration: none; font-weight: 600; margin-top: 16px; }
        .secondary-link:hover { color: #334155; }
        .error-text { font-size: 11px; color: #b91c1c; margin-top: 4px; text-align: left; display: block; }
        .footer-text { margin-top: 18px; font-size: 11px; color: #9ca3af; text-align: center; }
        .footer-text a { color: #2563eb; text-decoration: none; font-weight: 600; }
        .footer-text a:hover { text-decoration: underline; }
        .password-label-row { display: flex; align-items: center; gap: 8px; }
        .password-label-row .form-label { margin-bottom: 0; }
        .password-help { position: relative; display: inline-flex; align-items: center; }
        .password-help-trigger { width: 18px; height: 18px; border: 0; background: transparent; color: #64748b; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: color 0.2s ease; padding: 0; }
        .password-help-trigger:hover { color: #0f172a; }
        .password-help-trigger:focus-visible { outline: 2px solid #22d3ee; outline-offset: 2px; border-radius: 999px; }
        .password-help-tooltip { position: absolute; top: calc(100% + 10px); left: 0; z-index: 50; width: 260px; padding: 14px 16px; border-radius: 16px; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18); color: #475569; font-size: 12px; line-height: 1.5; display: none; }
        .password-help-tooltip.is-visible { display: block; }
        .password-help-title { font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .password-help-tooltip ul { margin: 0; padding-left: 16px; }
        .password-help-tooltip li { margin-bottom: 4px; }
        .password-help-tooltip li:last-child { margin-bottom: 0; }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-badge">
                <div class="logo-badge-inner">
                    <img src="{{ asset('images/logo/logo_email_push.png') }}" alt="RMA Auto">
                </div>
            </div>

            <h1 class="title">Reimposta la password</h1>
            <p class="subtitle">
                Inserisci la tua email e scegli una nuova password per completare il recupero dell’account.
            </p>

            <form method="POST" action="{{ route('password.store') }}" id="resetPasswordForm">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="form-control-wrap">
                        <span class="icon-left"><i class="fa fa-envelope"></i></span>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $request->email) }}"
                            placeholder="nome.cognome@dominio.it"
                            required
                            autofocus
                            autocomplete="username"
                        >
                    </div>
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="password-label-row">
                        <label class="form-label" for="password">Nuova password</label>

                        <div class="password-help" id="passwordHelpWrapper">
                            <button
                                type="button"
                                id="passwordHelpTrigger"
                                class="password-help-trigger"
                                aria-label="Mostra requisiti password"
                                aria-expanded="false"
                                aria-controls="passwordHelpTooltip"
                            >
                                <i class="fa-solid fa-circle-info"></i>
                            </button>

                            <div id="passwordHelpTooltip" class="password-help-tooltip">
                                <p class="password-help-title">Requisiti password</p>
                                <ul>
                                    <li>Almeno 10 caratteri</li>
                                    <li>Almeno una lettera maiuscola</li>
                                    <li>Almeno una lettera minuscola</li>
                                    <li>Almeno un numero</li>
                                    <li>Almeno 2 caratteri speciali</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="form-control-wrap">
                        <span class="icon-left"><i class="fa-solid fa-lock"></i></span>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Inserisci la nuova password"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Conferma password</label>
                    <div class="form-control-wrap">
                        <span class="icon-left"><i class="fa-solid fa-shield-heart"></i></span>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            placeholder="Ripeti la nuova password"
                            required
                            autocomplete="new-password"
                        >
                    </div>
                    @error('password_confirmation')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-submit">
                    Reimposta password <i class="fa-solid fa-key"></i>
                </button>
            </form>

            <a href="{{ route('login') }}" class="secondary-link" data-transition-link>
                <i class="fa-solid fa-arrow-left"></i>
                Torna al login
            </a>

            <p class="footer-text">
                Hai bisogno di supporto? <a href="mailto:info@expir0.it">Contatta l'Amministratore</a>
            </p>
        </div>
        <div
            id="weakPasswordModal"
            class="modal-overlay {{ $showWeakPasswordModal ? 'modal-visible modal-active' : '' }}"
        >
            <div onclick="closeWeakPasswordModal()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>

            <div class="modal-container w-full max-w-md">
                <div class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                    <div class="p-6 text-center pt-8 pb-6">
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-5">
                            <i class="fa-solid fa-triangle-exclamation text-3xl text-red-500"></i>
                        </div>

                        <h3 class="text-2xl font-black text-slate-800 mb-2">Password non valida</h3>

                        <p class="text-sm text-slate-600">
                            Inserisci una password più sicura con almeno 10 caratteri, una lettera maiuscola,
                            una minuscola, un numero e almeno 2 caratteri speciali.
                        </p>

                        <button
                            type="button"
                            onclick="closeWeakPasswordModal()"
                            class="mt-6 px-6 py-2 bg-red-500 text-white rounded-lg font-bold hover:bg-red-600 transition"
                        >
                            Chiudi
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div id="successPasswordModal" class="modal-overlay">
            <div onclick="closeSuccessPasswordModal()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>

            <div class="modal-container w-full max-w-md">
                <div class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                    <div class="p-6 text-center pt-8 pb-6">
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-emerald-50 mb-5 animate-pulse">
                            <i class="fa-solid fa-check text-3xl text-emerald-500"></i>
                        </div>

                        <h3 class="text-2xl font-black text-slate-800 mb-2">Perfetto!</h3>

                        <p class="text-sm text-slate-600">
                            La password è stata aggiornata correttamente. Ora puoi accedere con le nuove credenziali.
                        </p>

                        <button
                            type="button"
                            onclick="closeSuccessPasswordModal()"
                            class="mt-6 px-6 py-2 bg-emerald-500 text-white rounded-lg font-bold hover:bg-emerald-600 transition"
                        >
                            Chiudi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hasWeakPasswordModal = {{ $showWeakPasswordModal ? 'true' : 'false' }};
            const hasSuccessModal = @json((bool) session('status'));

            if (!hasWeakPasswordModal && !hasSuccessModal) {
                requestAnimationFrame(() => {
                    document.body.classList.add('page-ready');
                });
            } else {
                document.body.classList.add('page-ready');
            }

            document.querySelectorAll('[data-transition-link]').forEach(link => {
                link.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (!href || href.startsWith('#')) return;

                    e.preventDefault();
                    document.body.classList.remove('page-ready');
                    document.body.classList.add('page-leaving');

                    setTimeout(() => {
                        window.location.href = href;
                    }, 220);
                });
            });

            /* document.querySelectorAll('[data-transition-form]').forEach(form => {
                form.addEventListener('submit', function () {
                    document.body.classList.remove('page-ready');
                    document.body.classList.add('page-leaving');
                });
            }); */

            const wrapper = document.getElementById('passwordHelpWrapper');
            const trigger = document.getElementById('passwordHelpTrigger');
            const tooltip = document.getElementById('passwordHelpTooltip');

            if (wrapper && trigger && tooltip) {
                let hideTimeout;

                function openTooltip() {
                    clearTimeout(hideTimeout);
                    tooltip.classList.add('is-visible');
                    trigger.setAttribute('aria-expanded', 'true');
                }

                function closeTooltip() {
                    tooltip.classList.remove('is-visible');
                    trigger.setAttribute('aria-expanded', 'false');
                }

                function scheduleCloseTooltip() {
                    hideTimeout = setTimeout(() => {
                        if (!tooltip.matches(':hover') && !trigger.matches(':hover')) {
                            closeTooltip();
                        }
                    }, 120);
                }

                function toggleTooltip(e) {
                    e.preventDefault();

                    if (tooltip.classList.contains('is-visible')) {
                        closeTooltip();
                    } else {
                        openTooltip();
                    }
                }

                trigger.addEventListener('mouseenter', openTooltip);
                trigger.addEventListener('focus', openTooltip);
                trigger.addEventListener('click', toggleTooltip);

                trigger.addEventListener('mouseleave', scheduleCloseTooltip);
                tooltip.addEventListener('mouseenter', openTooltip);
                tooltip.addEventListener('mouseleave', scheduleCloseTooltip);

                document.addEventListener('click', function (e) {
                    if (!wrapper.contains(e.target)) {
                        closeTooltip();
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        closeTooltip();
                    }
                });
            }

            if (hasSuccessModal) {
                openSuccessPasswordModal();

                setTimeout(() => {
                    document.body.classList.remove('page-ready');
                    document.body.classList.add('page-leaving');
                }, 1700);

                setTimeout(() => {
                    window.location.href = '{{ route('login') }}';
                }, 2200);
            }
        });

        function isAnyModalOpen() {
            return document.querySelector('.modal-overlay.modal-visible') !== null;
        }

        function closeWeakPasswordModal() {
            const modal = document.getElementById('weakPasswordModal');
            modal.classList.remove('modal-active');
            modal.classList.add('modal-out');

            setTimeout(() => {
                modal.classList.remove('modal-visible', 'modal-out');
            }, 400);
        }

        function openSuccessPasswordModal() {
            const modal = document.getElementById('successPasswordModal');
            modal.classList.remove('modal-out');
            modal.classList.add('modal-visible', 'modal-active');
        }

        function closeSuccessPasswordModal() {
            const modal = document.getElementById('successPasswordModal');
            modal.classList.remove('modal-active');
            modal.classList.add('modal-out');

            setTimeout(() => {
                modal.classList.remove('modal-visible', 'modal-out');
            }, 400);
        }
    </script>
</body>
</html>