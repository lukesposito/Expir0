<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class Check2FA
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // 1. Se l'utente non è loggato, non ha la 2FA, oppure ha GIÀ inserito il codice in questa sessione -> Vai avanti!
        if (!$user || !$user->google2fa_enabled || session('2fa_verified')) {
            return $next($request);
        }

        // 2. Altrimenti, dobbiamo bloccarllo. Per evitare loop infiniti, controlliamo che non sia già sulla pagina del codice o stia facendo il logout
        if ($request->routeIs('2fa.index', '2fa.verify', 'logout')) {
            return $next($request);
        }

        // 3. Rimandalo alla pagina di inserimento codice
        return redirect()->route('2fa.index');
    }
}
