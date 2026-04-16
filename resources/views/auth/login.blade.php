{{-- resources/views/auth/login.blade.php --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login Expir0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Nunito', system-ui, -apple-system, sans-serif; }
        body { min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .overlay { position: fixed; inset: 0; background: radial-gradient(circle at 15% 15%, rgba(251, 191, 36, 0.65), transparent 55%), radial-gradient(circle at 85% 85%, rgba(37, 99, 235, 0.65), transparent 55%); backdrop-filter: blur(2px); }
        .login-wrapper { position: relative; z-index: 1; width: 100%; max-width: 420px; padding: 24px; }
        .login-card { position: relative; 
            width: 100%; 
            background: linear-gradient(180deg, #ffffff 0%, #f3f4f6 100%); 
            border-radius: 24px; 
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.45); 
            padding: 64px 40px 32px 40px; 
            text-align: center; 
            /* Ho rimosso "overflow: hidden;" che causava il taglio del logo! */
        }
        .logo-badge { position: absolute; top: -76px; left: 50%; transform: translateX(-50%); width: 152px; height: 144px; border-radius: 50%; background: radial-gradient(circle at 20% 0, #fef9c3, #facc15); box-shadow: 0 14px 35px rgba(15, 23, 42, 0.45); display: flex; align-items: center; justify-content: center; padding: 10px; }
        .logo-badge-inner { width: 100%; height: 100%; border-radius: 50%; background: #ffffff; border: 4px solid #1e3a8a; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .logo-badge-inner img { width: 96%; height: 96%; object-fit: contain; object-position: center; mask-image: radial-gradient(circle 85% at center, white 60%, transparent 70%); -webkit-mask-image: radial-gradient(circle 85% at center, white 60%, transparent 70%); }
        .title { margin-top: 16px; font-size: 20px; font-weight: 700; color: #0f172a; }
        .subtitle { margin-top: 2px; font-size: 13px; font-weight: 400; color: #6b7280; }
        
        .user-badge { display: inline-flex; align-items: center; gap: 8px; background: #e2e8f0; padding: 4px 12px 4px 6px; border-radius: 999px; font-size: 13px; font-weight: 600; color: #334155; margin-top: 16px; cursor: pointer; transition: background 0.2s; }
        .user-badge:hover { background: #cbd5e1; }
        .user-badge i { background: #fff; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #475569; }

        form { margin-top: 24px; text-align: left; }
        .form-group { margin-bottom: 16px; position: relative; }
        .form-label { font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 4px; display: block; text-transform: uppercase; letter-spacing: 0.04em; }
        .form-control-wrap { position: relative; display: flex; align-items: center; border-radius: 999px; padding: 0 14px; background: linear-gradient(90deg, #e5e7eb, #f9fafb); box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.7); transition: all 0.3s ease; }
        .form-control-wrap:focus-within { box-shadow: inset 0 0 0 2px #2563eb; background: #ffffff; }
        .form-control-wrap input { flex: 1; border: none; background: transparent; padding: 10px 0; font-size: 14px; color: #111827; outline: none; width: 100%; }
        .form-control-wrap input::placeholder { color: #9ca3af; }
        .icon-left { color: #2563eb; margin-right: 8px; display: flex; align-items: center; justify-content: center; }
        .toggle-password { cursor: pointer; color: #9ca3af; font-size: 13px; margin-left: 8px; display: flex; align-items: center; }
        .toggle-password:hover { color: #4b5563; }
        
        .btn-submit { margin-top: 18px; width: 100%; border: none; border-radius: 999px; padding: 11px 18px; font-size: 14px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #ffffff; background: linear-gradient(90deg, #f59e0b, #2563eb); box-shadow: 0 12px 30px rgba(37, 99, 235, 0.65); cursor: pointer; transition: all 0.2s ease-out; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .btn-submit:hover { filter: brightness(1.05); box-shadow: 0 18px 40px rgba(37, 99, 235, 0.8); }
        .btn-submit:active { transform: translateY(1px); box-shadow: 0 10px 24px rgba(37, 99, 235, 0.7); }

        /* Stile Lista Metodi di Accesso (Microsoft Style) */
        .auth-methods-list { margin-top: 24px; text-align: left; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: white; }
        .auth-method-item { display: flex; align-items: center; gap: 16px; padding: 16px; border-bottom: 1px solid #e2e8f0; cursor: pointer; transition: background 0.2s; }
        .auth-method-item:last-child { border-bottom: none; }
        .auth-method-item:hover { background: #f8fafc; }
        .auth-method-icon { width: 32px; font-size: 20px; color: #334155; text-align: center; }
        .auth-method-text { flex: 1; font-size: 14px; font-weight: 600; color: #1e293b; }

        .footer-text { margin-top: 18px; font-size: 11px; color: #9ca3af; text-align: center; }
        .footer-text a { color: #2563eb; text-decoration: none; font-weight: 600; }
        .footer-text a:hover { text-decoration: underline; }
        .error-text { font-size: 11px; color: #b91c1c; margin-top: 4px; text-align: left; display: block; }
        .hidden-step { display: none; }

        @media (max-width: 480px) { .login-card { padding: 56px 20px 28px 20px; } .logo-badge { width: 108px; height: 108px; top: -54px; } }
    </style>
</head>
<body>
<div class="overlay"></div>

<div class="login-wrapper">
    <div class="login-card">
        <div class="logo-badge">
            <div class="logo-badge-inner">
                <img src="{{ asset('images/logo/Logo_RMA.jpg') }}" alt="RMA Auto">
            </div>
        </div>

        <div id="header-default">
            <h1 class="title">Reminder Management Area</h1>
            <p class="subtitle">Accedi al gestionale per gestire le tue scadenze</p>
        </div>

        <div id="header-user" class="hidden-step">
            <h1 class="title">Verificare l'identità</h1>
            <div class="user-badge" onclick="resetToEmailStep()" title="Cambia account">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span id="display-email">utente@email.it</span>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            {{-- STEP 1: INSERIMENTO EMAIL E BOTTONE RAPIDO PASSKEY --}}
            <div id="step-email">
                <div class="form-group mt-6">
                    <label class="form-label" for="email">Email</label>
                    <div class="form-control-wrap">
                        <span class="icon-left"><i class="fa fa-envelope"></i></span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nome.cognome@dominio.it" autofocus>
                    </div>
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                    <span id="email-js-error" class="error-text hidden-step">Inserisci un'email valida.</span>
                </div>

                <button type="button" id="btn-avanti" class="btn-submit" onclick="checkPasskeyFlow()">
                    Avanti <i class="fa-solid fa-arrow-right"></i>
                </button>

                {{-- Divider e Bottone Passkey Immediato --}}
                <div id="manual-passkey-section">
                    <div class="mt-6 flex items-center justify-between">
                        <span class="border-b w-1/5 lg:w-1/4"></span>
                        <span class="text-xs text-center text-gray-500 uppercase">Oppure</span>
                        <span class="border-b w-1/5 lg:w-1/4"></span>
                    </div>
                    <div class="mt-6">
                        <button type="button" onclick="triggerPasskey()" class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-slate-800 hover:bg-slate-900 focus:outline-none transition">
                            <i class="fa-solid fa-fingerprint text-lg"></i> Accedi con FaceID / Impronta
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 2: SCELTA METODO (Stile Microsoft) --}}
            <div id="step-methods" class="hidden-step">
                <div class="auth-methods-list shadow-sm">
                    <div class="auth-method-item" onclick="triggerPasskey()">
                        <div class="auth-method-icon"><i class="fa-solid fa-user-shield"></i></div>
                        <div class="auth-method-text">Viso, impronta digitale, PIN o chiave di sicurezza</div>
                    </div>
                    
                    <div class="auth-method-item" onclick="showPasswordStep()">
                        <div class="auth-method-icon"><i class="fa-solid fa-keyboard"></i></div>
                        <div class="auth-method-text">Usare la password</div>
                    </div>
                </div>
                
                <p class="footer-text mt-6">
                    Seleziona un'opzione per confermare che sei tu.
                </p>
            </div>

            {{-- STEP 3: INSERIMENTO PASSWORD --}}
            <div id="step-password" class="hidden-step">
                <div class="form-group mt-6">
                    <label class="form-label" for="password">Password</label>
                    <div class="form-control-wrap">
                        <span class="icon-left"><i class="fa fa-lock"></i></span>
                        <input id="password" type="password" name="password" placeholder="Inserisci la tua password">
                        <span class="toggle-password" onclick="togglePassword()">
                            <i class="fa fa-eye"></i>
                        </span>
                    </div>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                    
                    <div style="text-align: right; margin-top: 8px;">
                        <a href="{{ route('password.request') }}" style="font-size: 11px; color: #2563eb; text-decoration: none; font-weight: 600;">Password dimenticata?</a>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    Accedi <i class="fa-solid fa-right-to-bracket"></i>
                </button>

                <div style="text-align: center; margin-top: 16px;" id="back-to-methods-container">
                    <a href="#" onclick="showMethodsStep(event)" style="font-size: 12px; color: #6b7280; text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Accedi in modo diverso</a>
                </div>
            </div>

            <p class="footer-text" id="footer-contact">
                Non hai ancora un account? <a href="mailto:info@expir0.it">Contatta l'Amministratore</a>
            </p>
        </form>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
    }

    @if($errors->has('password') || $errors->has('email'))
        document.addEventListener("DOMContentLoaded", function() {
            const emailVal = document.getElementById('email').value;
            if(emailVal) {
                document.getElementById('display-email').innerText = emailVal;
                document.getElementById('header-default').style.display = 'none';
                document.getElementById('header-user').style.display = 'block';
                showPasswordStep();
            }
        });
    @endif

    document.getElementById('email').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            checkPasskeyFlow();
        }
    });

    async function checkPasskeyFlow() {
        const emailInput = document.getElementById('email').value;
        const errorDiv = document.getElementById('email-js-error');
        const btn = document.getElementById('btn-avanti');
        
        if (!emailInput || !emailInput.includes('@')) {
            errorDiv.style.display = 'block';
            return;
        }
        
        errorDiv.style.display = 'none';
        const originalBtnText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Controllo...';
        btn.disabled = true;

        try {
            const response = await fetch('{{ route("check.passkey") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ email: emailInput })
            });

            const data = await response.json();
            btn.innerHTML = originalBtnText;
            btn.disabled = false;

            document.getElementById('display-email').innerText = emailInput;
            document.getElementById('header-default').style.display = 'none';
            document.getElementById('header-user').style.display = 'block';
            document.getElementById('footer-contact').style.display = 'none';

            if (data.has_passkey) {
                showMethodsStep();
                setTimeout(() => triggerPasskey(), 300);
            } else {
                showPasswordStep();
                document.getElementById('back-to-methods-container').style.display = 'none';
            }
        } catch (error) {
            console.error("Errore di rete:", error);
            showPasswordStep();
        }
    }

    function showMethodsStep(e = null) {
        if(e) e.preventDefault();
        document.getElementById('step-email').style.display = 'none';
        document.getElementById('step-password').style.display = 'none';
        document.getElementById('step-methods').style.display = 'block';
    }

    function showPasswordStep() {
        document.getElementById('step-email').style.display = 'none';
        document.getElementById('step-methods').style.display = 'none';
        document.getElementById('step-password').style.display = 'block';
        document.getElementById('password').setAttribute('required', 'required');
        setTimeout(() => document.getElementById('password').focus(), 100);
    }

    function resetToEmailStep() {
        document.getElementById('header-user').style.display = 'none';
        document.getElementById('header-default').style.display = 'block';
        document.getElementById('footer-contact').style.display = 'block';
        
        document.getElementById('step-methods').style.display = 'none';
        document.getElementById('step-password').style.display = 'none';
        document.getElementById('step-email').style.display = 'block';
        
        document.getElementById('password').removeAttribute('required');
        document.getElementById('email').focus();
    }

    async function triggerPasskey() {
        if (typeof window.Webpass === 'undefined') {
            alert('Modulo di sicurezza in caricamento. Riprova tra un istante.');
            return;
        }

        try {
            // Laragear/WebAuthn capisce in automatico che se richiami assert()
            // dal bottone diretto farà un login "Usernameless" scoprendo l'identità dal dispositivo
            const { success, error } = await window.Webpass.assert("/webauthn/login/options", "/webauthn/login");
            if (success) {
                window.location.href = '{{ route("dashboard") }}'; 
            } else {
                console.log("Passkey annullata dall'utente.");
            }
        } catch (e) {
            console.error("ERRORE WEB-AUTHN:", e);
        }
    }
</script>
</body>
</html>