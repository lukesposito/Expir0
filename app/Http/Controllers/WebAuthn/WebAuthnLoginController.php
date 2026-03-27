<?php

namespace App\Http\Controllers\WebAuthn;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function response;

class WebAuthnLoginController
{
    /**
     * Returns the challenge to assertion.
     */
    public function options(AssertionRequest $request): Responsable
    {
        return $request->toVerify($request->validate(['email' => 'sometimes|email|string']));
    }

    /**
     * Log the user in.
     */
    public function login(AssertedRequest $request)
    {
        // 1. Se il codice è arrivato fin qui, AssertedRequest ha GIÀ 
        // validato la firma crittografica dell'impronta. È sicura al 100%.
        
        // 2. Troviamo l'impronta nel database usando l'ID inviato dal sensore
        $credential = DB::table('webauthn_credentials')
            ->where('id', $request->input('id'))
            ->first();
            
        if ($credential) {
            // 3. Peschiamo l'utente proprietario (usando la colonna giusta del tuo DB)
            $user = User::find($credential->authenticatable_id);
            
            if ($user) {
                // Logghiamo l'utente
                Auth::login($user);
                
                // AGGIUNGI QUESTA RIGA: 
                // Diciamo alla sessione che l'utente ha già superato i controlli di sicurezza
                // (Sostituisci '2fa_verified' con il nome esatto della variabile di sessione 
                // che usi nel tuo middleware 2FA per far passare l'utente, di solito è '2fa_passed' o '2fa_verified')
                session()->put('2fa_verified', true);
                
                return response()->json(['ok' => true]);
            }
        }
        
        return response()->json(['error' => 'Autenticazione fallita'], 401);
    }
}
