@vite(['resources/css/app.css', 'resources/js/app.js'])
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Verifica 2FA Expir0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Nunito', system-ui, -apple-system, sans-serif; }
        body { min-height: 100vh; display: flex; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.35s ease, transform 0.35s ease;}
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
        .form-control-wrap input { flex: 1; border: none; background: transparent; padding: 12px 0; font-size: 24px; color: #111827; outline: none; width: 100%; text-align: center; letter-spacing: 0.5em; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-weight: 700; }
        .form-control-wrap input::placeholder { color: #9ca3af; letter-spacing: 0.2em; font-size: 18px; }
        .icon-left { color: #2563eb; margin-right: 8px; display: flex; align-items: center; justify-content: center; }

        .btn-submit { margin-top: 18px; width: 100%; border: none; border-radius: 999px; padding: 11px 18px; font-size: 14px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #ffffff; background: linear-gradient(90deg, #00ddff, #0cfd99); box-shadow: 0 12px 30px rgba(37, 99, 235, 0.65); cursor: pointer; transition: all 0.2s ease-out; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .btn-submit:hover { filter: brightness(1.05); box-shadow: 0 18px 40px rgba(37, 99, 235, 0.8); }
        .btn-submit:active { transform: translateY(1px); box-shadow: 0 10px 24px rgba(37, 99, 235, 0.7); }

        .btn-back-login { width: 150px; font-size: 12px; border-radius: 999px; justify-content: center; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; text-decoration: none; font-weight: 600; color: #ffffff; background: linear-gradient(90deg, #0048ff, #020074); box-shadow: 0 12px 30px rgba(37, 99, 235, 0.65); cursor: pointer; transition: color 0.2s ease;}
        .btn-back-login:hover { filter: brightness(1.05); box-shadow: 0 12px 20px rgba(37, 99, 235, 0.8); }
        .btn-back-login:active { transform: translateY(1px); box-shadow: 0 5px 14px rgba(37, 99, 235, 0.7); }

        .error-text { font-size: 11px; color: #b91c1c; margin-top: 6px; text-align: center; display: block; }
        .footer-text { margin-top: 18px; font-size: 11px; color: #9ca3af; text-align: center; }
        .footer-text a { color: #2563eb; text-decoration: none; font-weight: 600; }
        .footer-text a:hover { text-decoration: underline; }

        .actions-row { display: flex; justify-content: center; align-items: center; gap: 16px; margin-top: 16px; }
        .actions-row form { margin-top: 0; }

        @media (max-width: 480px) {
            .login-card { padding: 56px 20px 28px 20px; top: -54px; }
            .logo-badge { width: 108px; height: 108px; top: -54px; }
            .form-control-wrap input { font-size: 20px; }
            .actions-row { flex-direction: column-reverse; align-items: stretch; }
            .btn-back-login { justify-content: center; }
        }
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

            <h1 class="title">Autenticazione a Due Fattori</h1>
            <p class="subtitle">
                Apri la tua app Authenticator e inserisci il codice di verifica a 6 cifre per completare l'accesso.
            </p>

            <form method="POST" action="{{ route('2fa.verify') }}" data-transition-form>
                @csrf

                <div class="form-group mt-6">
                    <label class="form-label" for="otp">Codice a 6 cifre</label>
                    <div class="form-control-wrap">
                        <span class="icon-left"><i class="fa-solid fa-shield-halved"></i></span>
                        <input
                            id="otp"
                            type="text"
                            name="otp"
                            value="{{ old('otp') }}"
                            placeholder="000000"
                            required
                            autofocus
                            autocomplete="one-time-code"
                            maxlength="6"
                            inputmode="numeric"
                        >
                    </div>
                    @error('otp')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-submit">
                    Verifica e Accedi <i class="fa-solid fa-right-to-bracket"></i>
                </button>
            </form>

            <div class="actions-row">
                <a
                    href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="btn-back-login"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Torna al Login
                </a>
            </div>

            <p class="footer-text">
                Hai problemi con il codice? <a href="mailto:info@expir0.it">Contatta l'Amministratore</a>
            </p>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
    </form>
</body>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        requestAnimationFrame(() => {
            document.body.classList.add('page-ready');
        });

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

        document.querySelectorAll('[data-transition-form]').forEach(form => {
            form.addEventListener('submit', function () {
                document.body.classList.remove('page-ready');
                document.body.classList.add('page-leaving');
            });
        });
    });
</script>
</html>