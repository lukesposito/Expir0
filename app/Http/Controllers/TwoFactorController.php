<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    // Mostra la pagina grafica dove l'utente digita il codice al login
    public function index()
    {
        return view('auth.2fa');
    }

    // Verifica il codice inserito al login
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6'
        ]);

        $user = auth()->user();
        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        // Se il codice è corretto...
        if ($google2fa->verifyKey($user->google2fa_secret, $request->otp)) {
            // Sblocchiamo la sessione!
            session(['2fa_verified' => true]);
            
            // Lo mandiamo alla Dashboard
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Se il codice è sbagliato, torna indietro con l'errore
        return back()->withErrors(['otp' => 'Codice errato o scaduto. Riprova.']);
    }

    // Genera il QR Code da scansionare e la chiave segreta
    public function generateSecret(Request $request)
    {
        $user = auth()->user();
        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        // Genera una nuova chiave segreta
        $secret = $google2fa->generateSecretKey();
        $user->google2fa_secret = $secret;
        $user->save();

        // Puliamo il nome dell'app da eventuali spazi per evitare crash
        $appName = str_replace(' ', '', config('app.name', 'Gestionale'));
        
        // Genera l'URL per l'app Authenticator
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            $appName,
            $user->email,
            $secret
        );

        // Genera l'immagine SVG del QR Code
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return response()->json([
            'secret' => $secret,
            'qr_code' => $qrCodeSvg // <--- Niente più utf8_encode() !
        ]);
    }

    // Conferma il codice a 6 cifre e abilita la MFA
    public function confirm(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6'
        ]);

        $user = auth()->user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->otp);

        if ($valid) {
            $user->google2fa_enabled = true;
            $user->save();
            return response()->json(['message' => 'Autenticazione a due fattori attivata con successo!']);
        }

        return response()->json([
            'errors' => ['otp' => ['Il codice inserito non è corretto. Riprova.']]
        ], 422);
    }

    // Disabilita la MFA
    public function disable(Request $request)
    {
        $user = auth()->user();
        $user->google2fa_enabled = false;
        $user->google2fa_secret = null;
        $user->save();

        return response()->json(['message' => 'Autenticazione a due fattori disattivata.']);
    }
}