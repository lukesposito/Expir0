<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{
    /**
     * Salva l'iscrizione alle notifiche Push per il dispositivo corrente
     */
    public function subscribe(Request $request)
    {
        $user = Auth::user();

        // Validazione dei dati inviati dal browser
        $this->validate($request, [
            'endpoint'    => 'required|string',
            'keys.auth'   => 'required|string',
            'keys.p256dh' => 'required|string'
        ]);

        $endpoint = $request->endpoint;
        $token = $request->keys['auth'];
        $key = $request->keys['p256dh'];

        // Il pacchetto fa tutto il lavoro sporco per noi!
        $user->updatePushSubscription($endpoint, $key, $token);

        return response()->json(['success' => true, 'message' => 'Notifiche attivate con successo!'], 200);
    }

    /**
     * Rimuove l'iscrizione per questo dispositivo
     */
    public function unsubscribe(Request $request)
    {
        $user = Auth::user();
        
        $this->validate($request, [
            'endpoint' => 'required|string'
        ]);

        $user->deletePushSubscription($request->endpoint);

        return response()->json(['success' => true, 'message' => 'Notifiche disattivate per questo dispositivo.'], 200);
    }
}