@vite(['resources/css/app.css', 'resources/js/app.js'])
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Recupera Password Expir0</title>
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
        .form-control-wrap.has-error { box-shadow: inset 0 0 0 2px #dc2626; background: #fff; }

        .icon-left { color: #2563eb; margin-right: 8px; display: flex; align-items: center; justify-content: center; }
        .btn-submit { margin-top: 18px; width: 100%; border: none; border-radius: 999px; padding: 11px 18px; font-size: 14px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #ffffff; background: linear-gradient(90deg, #00ddff, #0cfd99); box-shadow: 0 12px 30px rgba(37, 99, 235, 0.65); cursor: pointer; transition: all 0.2s ease-out; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .btn-submit:hover { filter: brightness(1.05); box-shadow: 0 18px 40px rgba(37, 99, 235, 0.8); }
        .btn-submit:active { transform: translateY(1px); box-shadow: 0 10px 24px rgba(37, 99, 235, 0.7); }
        .btn-submit:disabled { cursor: not-allowed; opacity: 0.8; }
        .secondary-link { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #6b7280; text-decoration: none; font-weight: 600; margin-top: 16px; }
        .secondary-link:hover { color: #334155; }
        .error-text { font-size: 11px; color: #b91c1c; margin-top: 4px; text-align: left; display: block; }
        .status-box { margin-top: 18px; padding: 12px 14px; border-radius: 14px; background: #ecfeff; border: 1px solid #a5f3fc; color: #155e75; font-size: 12px; line-height: 1.5; text-align: left; }
        .footer-text { margin-top: 18px; font-size: 11px; color: #9ca3af; text-align: center; }
        .footer-text a { color: #2563eb; text-decoration: none; font-weight: 600; }
        .footer-text a:hover { text-decoration: underline; }

        .modal-overlay { position: fixed; inset: 0; z-index: 9999; display: none; align-items: center; justify-content: center; padding: 1rem; }
        .modal-overlay.modal-visible { display: flex; }
        .modal-backdrop { position: fixed; inset: 0; background: rgba(0, 0, 0, 0); }
        .modal-active .modal-backdrop { animation: fadeIn 0.4s ease-out forwards; }
        .modal-out .modal-backdrop { animation: fadeOut 0.4s ease-out forwards; }
        .modal-container { position: relative; z-index: 1000; width: 100%; }
        .modal-content-wrapper { transform: scale(0); }
        .modal-active .modal-content-wrapper { animation: blowUpModal 0.4s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        .modal-out .modal-content-wrapper { animation: blowUpModalTwo 0.4s cubic-bezier(0.165, 0.840, 0.440, 1.000) forwards; }
        @keyframes fadeIn { 0% { background: rgba(0, 0, 0, 0); } 100% { background: rgba(15, 23, 42, 0.6); } }
        @keyframes fadeOut { 0% { background: rgba(15, 23, 42, 0.6); } 100% { background: rgba(0, 0, 0, 0); } }
        @keyframes blowUpModal { 0% { transform: scale(0); } 100% { transform: scale(1); } }
        @keyframes blowUpModalTwo { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(0); opacity: 0; } }
        @media (max-width: 480px) { .login-card { padding: 56px 20px 28px 20px; top: -54px; } .logo-badge { width: 108px; height: 108px; top: -54px; } }

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

            <h1 class="title">Recupera la password</h1>
            <p class="subtitle">
                Inserisci il tuo indirizzo email e ti invieremo un link per reimpostare la password.
            </p>

            @if (session('status'))
                <div class="status-box">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm" novalidate>
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="form-control-wrap">
                        <span class="icon-left"><i class="fa fa-envelope"></i></span>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nome.cognome@dominio.it"
                            required
                            autofocus
                        >
                    </div>
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-submit" id="btn-forgot-password">
                    Invia link di reset <i class="fa-solid fa-paper-plane"></i>
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
        <div id="forgotSuccessModal" class="modal-overlay">
            <div onclick="closeForgotSuccessModal()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>

            <div class="modal-container w-full max-w-md">
                <div class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                    <div class="p-6 text-center pt-8 pb-6">
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-emerald-50 mb-5 animate-pulse">
                            <i class="fa-solid fa-paper-plane text-3xl text-emerald-500"></i>
                        </div>

                        <h3 class="text-2xl font-black text-slate-800 mb-2">Perfetto!</h3>
                        <p class="text-sm text-slate-600">
                            Se l'indirizzo email è presente nel sistema, ti abbiamo inviato il link per reimpostare la password.
                        </p>

                        <button
                            type="button"
                            onclick="closeForgotSuccessModal(true)"
                            class="mt-6 px-6 py-2 bg-emerald-500 text-white rounded-lg font-bold hover:bg-emerald-600 transition"
                        >
                            Chiudi
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="forgotErrorModal" class="modal-overlay">
            <div onclick="closeForgotErrorModal()" class="modal-backdrop bg-slate-900/60 backdrop-blur-sm"></div>

            <div class="modal-container w-full max-w-md">
                <div class="modal-content-wrapper bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                    <div class="p-6 text-center pt-8 pb-6">
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-5">
                            <i class="fa-solid fa-triangle-exclamation text-3xl text-red-500"></i>
                        </div>

                        <h3 class="text-2xl font-black text-slate-800 mb-2">Attenzione</h3>
                        <p class="text-sm text-slate-600" id="forgotErrorMessage">
                            Si è verificato un problema durante l'invio del link di reset.
                        </p>

                        <button
                            type="button"
                            onclick="closeForgotErrorModal()"
                            class="mt-6 px-6 py-2 bg-red-500 text-white rounded-lg font-bold hover:bg-red-600 transition"
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
            requestAnimationFrame(() => {
                document.body.classList.add('page-ready');
            });

            document.querySelectorAll('[data-transition-link]').forEach(link => {
                link.addEventListener('click', function (e) {
                    if (isAnyModalOpen()) return;

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

            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const emailInput = document.getElementById('email');

            if (forgotPasswordForm) {
                forgotPasswordForm.addEventListener('submit', submitForgotPasswordAjax);
            }

            if (emailInput) {
                emailInput.addEventListener('input', clearForgotPasswordInlineError);
            }

            document.querySelectorAll('[data-transition-form]').forEach(form => {
                form.addEventListener('submit', function () {
                    document.body.classList.remove('page-ready');
                    document.body.classList.add('page-leaving');
                });
            });
        });

        function isAnyModalOpen() {
            return document.querySelector('.modal-overlay.modal-visible') !== null;
        }

        function openForgotSuccessModal() {
            const modal = document.getElementById('forgotSuccessModal');
            modal.classList.remove('modal-out');
            modal.classList.add('modal-visible', 'modal-active');
        }

        function closeForgotSuccessModal(redirectToLogin = false) {
            const modal = document.getElementById('forgotSuccessModal');
            modal.classList.remove('modal-active');
            modal.classList.add('modal-out');

            setTimeout(() => {
                modal.classList.remove('modal-visible', 'modal-out');

                if (redirectToLogin) {
                    document.body.classList.remove('page-ready');
                    document.body.classList.add('page-leaving');

                    setTimeout(() => {
                        window.location.href = '{{ route('login') }}';
                    }, 220);
                }
            }, 400);
        }

        function openForgotErrorModal(message = null) {
            const modal = document.getElementById('forgotErrorModal');
            const messageNode = document.getElementById('forgotErrorMessage');

            if (message && messageNode) {
                messageNode.textContent = message;
            }

            modal.classList.remove('modal-out');
            modal.classList.add('modal-visible', 'modal-active');
        }

        function closeForgotErrorModal() {
            const modal = document.getElementById('forgotErrorModal');
            modal.classList.remove('modal-active');
            modal.classList.add('modal-out');

            setTimeout(() => {
                modal.classList.remove('modal-visible', 'modal-out');
            }, 400);
        }

        function setForgotPasswordInlineError(message = '') {
            const emailInput = document.getElementById('email');
            const inputWrap = emailInput?.closest('.form-control-wrap');
            const existingError = document.getElementById('email-inline-error');

            if (inputWrap) {
                inputWrap.classList.toggle('has-error', Boolean(message));
            }

            if (emailInput) {
                emailInput.setAttribute('aria-invalid', message ? 'true' : 'false');
            }

            if (existingError) {
                existingError.remove();
            }

            if (message && emailInput) {
                const errorNode = document.createElement('span');
                errorNode.id = 'email-inline-error';
                errorNode.className = 'error-text';
                errorNode.textContent = message;
                emailInput.closest('.form-group')?.appendChild(errorNode);
            }
        }

        function clearForgotPasswordInlineError() {
            setForgotPasswordInlineError('');
        }


        async function submitForgotPasswordAjax(event) {
            event.preventDefault();

            const form = document.getElementById('forgotPasswordForm');
            const emailInput = document.getElementById('email');
            const btn = document.getElementById('btn-forgot-password');
            const email = emailInput.value.toLowerCase().trim();

            clearForgotPasswordInlineError();

            if (!email) {
                setForgotPasswordInlineError('Inserisci un indirizzo email valido.');
                emailInput.focus();
                return;
            }

            const originalBtnText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Invio in corso...';
            btn.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email })
                });

                const data = await response.json().catch(() => ({}));

                btn.innerHTML = originalBtnText;
                btn.disabled = false;

                if (response.ok) {
                    form.reset();
                    clearForgotPasswordInlineError();
                    openForgotSuccessModal();
                    return;
                }

                if (data.errors?.email?.length) {
                    setForgotPasswordInlineError(data.errors.email[0]);
                    emailInput.focus();
                    return;
                }

                if (data.message) {
                    openForgotErrorModal(data.message);
                    return;
                }

                openForgotErrorModal('Si è verificato un problema durante l\'invio del link di reset.');

            } catch (error) {
                btn.innerHTML = originalBtnText;
                btn.disabled = false;
                console.error('Errore forgot password AJAX:', error);
                openForgotErrorModal('Errore di connessione. Riprova tra qualche istante.');
            }
        }
    </script>
</body>
</html>