{{-- resources/views/auth/login.blade.php --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login RMA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Google Font (opzionale) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Nunito', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            /* background: url("{{ asset('images/showroom-blur.jpg') }}") center center / cover no-repeat fixed; */
        }

        /* overlay sfumato giallo/blu */
        .overlay {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 15% 15%, rgba(251, 191, 36, 0.65), transparent 55%),
                        radial-gradient(circle at 85% 85%, rgba(37, 99, 235, 0.65), transparent 55%);
            backdrop-filter: blur(2px);
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 24px;
        }

        .login-card {
            position: relative;
            width: 100%;
            background: linear-gradient(180deg, #ffffff 0%, #f3f4f6 100%);
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.45);
            padding: 64px 40px 32px 40px;
            text-align: center;
        }

        /* badge circolare logo */
        .logo-badge {
            position: absolute;
            top: -76px;          
            left: 50%;
            transform: translateX(-50%);
            width: 152px;        
            height: 144px;       
            border-radius: 50%;
            background: radial-gradient(circle at 20% 0, #fef9c3, #facc15);
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;       
        }

        .logo-badge-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #ffffff;
            border: 4px solid #1e3a8a;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-badge-inner img {
            width: 96%;
            height: 96%;
            object-fit: contain;
            object-position: center;
            mask-image: radial-gradient(circle 85% at center, white 60%, transparent 70%); /* ritaglia bordi se fuoriesce */
            -webkit-mask-image: radial-gradient(circle 85% at center, white 60%, transparent 70%);
        }

        .title {
            margin-top: 16px;
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }

        .subtitle {
            margin-top: 2px;
            font-size: 13px;
            font-weight: 400;
            color: #6b7280;
        }

        form {
            margin-top: 24px;
            text-align: left;
        }

        .form-group {
            margin-bottom: 16px;
            position: relative;
        }

        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 4px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .form-control-wrap {
            position: relative;
            display: flex;
            align-items: center;
            border-radius: 999px;
            padding: 0 14px;
            background: linear-gradient(90deg, #e5e7eb, #f9fafb);
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.7);
        }

        .form-control-wrap input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 10px 0;
            font-size: 14px;
            color: #111827;
            outline: none;
        }

        .form-control-wrap input::placeholder {
            color: #9ca3af;
        }

        .icon-left {
            color: #2563eb;
            margin-right: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-left i {
            font-size: 14px;
        }

        .toggle-password {
            cursor: pointer;
            color: #9ca3af;
            font-size: 13px;
            margin-left: 8px;
            display: flex;
            align-items: center;
        }

        .toggle-password:hover {
            color: #4b5563;
        }

        .forgot-link {
            font-size: 11px;
            color: #9ca3af;
            text-align: right;
            margin-top: 4px;
        }

        .forgot-link a {
            color: #2563eb;
            text-decoration: none;
        }

        .forgot-link a:hover {
            text-decoration: underline;
        }

        .btn-submit {
            margin-top: 18px;
            width: 100%;
            border: none;
            border-radius: 999px;
            padding: 11px 18px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #ffffff;
            background: linear-gradient(90deg, #f59e0b, #2563eb);
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.65);
            cursor: pointer;
            transition: transform 0.1s ease-out, box-shadow 0.1s ease-out, filter 0.1s ease-out;
        }

        .btn-submit:hover {
            filter: brightness(1.05);
            box-shadow: 0 18px 40px rgba(37, 99, 235, 0.8);
        }

        .btn-submit:active {
            transform: translateY(1px);
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.7);
        }

        .footer-text {
            margin-top: 18px;
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
        }

        .footer-text a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .error-text {
            font-size: 11px;
            color: #b91c1c;
            margin-top: 4px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 56px 20px 28px 20px;
            }

            .logo-badge {
                width: 108px;
                height: 108px;
                top: -54px;
            }
        }
    </style>
</head>
<body>
<div class="overlay"></div>

<div class="login-wrapper">
    <div class="login-card">
        <div class="logo-badge">
            <div class="logo-badge-inner">
                {{-- Sostituisci con il tuo logo (quello che vedi nello screenshot) --}}
                <img src="{{ asset('images/logo/Logo_RMA.jpg') }}" alt="RMA Auto">
            </div>
        </div>

        <h1 class="title">Reminder Management Area</h1>
        <p class="subtitle">Accedi al gestionale per gestire e vedere le tue scadenze</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <div class="form-control-wrap">
                    <span class="icon-left">
                        {{-- puoi sostituire con un'icona SVG o Font Awesome --}}
                        <i class="fa fa-envelope"></i>
                    </span>
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
                <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="form-control-wrap">
                    <span class="icon-left">
                        <i class="fa fa-lock"></i>
                    </span>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Inserisci la tua password"
                        required
                    >
                    <span class="toggle-password" onclick="togglePassword()">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
                <div class="forgot-link">
                    <a href="{{ route('password.request') }}">Password dimenticata?</a>
                </div>
                @error('password')
                <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-submit">
                ACCEDI AL GESTIONALE
            </button>

            <p class="footer-text">
                Non hai ancora un account?
                <a href="mailto:info@rma.it">Contatta l'Amministratore</a>
            </p>
        </form>

        <div class="mt-6 flex items-center justify-between">
            <span class="border-b w-1/5 lg:w-1/4"></span>
            <span class="text-xs text-center text-gray-500 uppercase">Oppure</span>
            <span class="border-b w-1/5 lg:w-1/4"></span>
        </div>

        <div class="mt-6">
            <button type="button" onclick="loginWithPasskey()" class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-slate-800 hover:bg-slate-900 focus:outline-none transition">
                <i class="fa-solid fa-fingerprint text-lg"></i>
                Accedi con FaceID / Impronta
            </button>
        </div>
    </div>
</div>

{{-- Font Awesome CDN (se usi le classi fa-*) --}}
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
      integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
      crossorigin="anonymous" referrerpolicy="no-referrer" />

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
    }

    window.loginWithPasskey = async function() {
        // Controllo di sicurezza se Vite non ha ancora caricato
        if (typeof window.Webpass === 'undefined') {
            alert('Il file di sicurezza sta ancora caricando. Riprova tra un secondo!');
            return;
        }

        try {
            // Chiamiamo assert dal file compilato
            const { success, user, error } = await window.Webpass.assert("/webauthn/login/options", "/webauthn/login");
            
            if (success) {
                window.location.href = '{{ route("dashboard") }}'; 
            } else {
                console.error("Errore Webpass:", error);
                alert('Accesso annullato o impronta non riconosciuta.');
            }
        } catch (e) {
            console.error("ERRORE DI CONNESSIONE:", e);
            alert('Errore di connessione al server.');
        }
    }
</script>
</body>
</html>