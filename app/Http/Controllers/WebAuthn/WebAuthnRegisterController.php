<?php

namespace App\Http\Controllers\WebAuthn;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;
use Illuminate\Support\Facades\DB;

class WebAuthnRegisterController
{
    /**
     * Returns a challenge to be verified by the user device.
     */
    public function options(AttestationRequest $request): Responsable
    {
        return $request
            ->fastRegistration()
//            ->userless()
//            ->allowDuplicates()
            ->toCreate();
    }

    /**
     * Registers a device for further WebAuthn authentication.
     */
    public function register(AttestedRequest $request): Response
    {
        // 1. Lasciamo salvare la chiave alla libreria standard
        $request->save();

        // 2. Troviamo la chiave appena creata usando i nomi corretti delle colonne (polimorfiche)
        $credential = DB::table('webauthn_credentials')
            ->where('authenticatable_id', $request->user()->getAuthIdentifier())
            ->where('authenticatable_type', get_class($request->user()))
            ->orderBy('created_at', 'desc')
            ->first();

        // 3. Aggiorniamo il nome (alias) con quello che hai scritto nel popup Javascript
        if ($credential) {
            DB::table('webauthn_credentials')
                ->where('id', $credential->id)
                ->update([
                    'alias' => $request->query('name', 'Chiave di Sicurezza')
                ]);
        }

        return response()->noContent();
    }
}