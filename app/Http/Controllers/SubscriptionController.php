<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $subscriptions = Subscription::with(['user', 'vehicle'])
            ->whereHas('deadlines', function($query) {
                $query->where('pagato', false);
            })
            ->orderBy('data_scadenza', 'asc')
            ->get()
            ->map(function ($sub) {
                $scadenza = Carbon::parse($sub->data_scadenza)->startOfDay();
                $oggi = Carbon::now()->startOfDay();
                
                return [
                    'id' => $sub->id,
                    'nome' => $sub->nome,
                    'categoria' => $sub->categoria,
                    'data_scadenza' => $sub->data_scadenza->format('Y-m-d'),
                    'data_scadenza_formatted' => $sub->data_scadenza->format('d/m/Y'),
                    'importo' => $sub->importo,
                    'importo_formatted' => $sub->importo ? number_format($sub->importo, 2, ',', '.') : '0,00',
                    'rinnovo_automatico' => (bool) $sub->rinnovo_automatico,
                    'giorni_preavviso' => (int) $sub->giorni_preavviso,
		    'notification_override' => $sub->notification_override,
                    'url_gestione' => $sub->url_gestione,
                    'note' => $sub->note,
                    'user_id' => $sub->user_id,
                    'user_name' => $sub->user ? $sub->user->name : 'N/D',
                    'vehicle_id' => $sub->vehicle_id,
                    'vehicle_desc' => $sub->vehicle ? $sub->vehicle->marca . ' ' . $sub->vehicle->modello . ' (' . $sub->vehicle->targa . ')' : null,
                    'giorni_rimanenti' => (int) $oggi->diffInDays($scadenza, false),
                ];
            });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['subscriptions' => $subscriptions]);
        }

        $users = User::orderBy('name')->get();
        $vehicles = Vehicle::orderBy('targa')->get();

        return view('subscriptions.index', compact('subscriptions', 'users', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'data_scadenza' => 'required|date',
            'importo' => 'nullable|numeric|min:0',
            'giorni_preavviso' => 'nullable|integer|min:0',
            'url_gestione' => 'nullable|url',
            'note' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'notification_override' => 'nullable|string',
        ]);

        // Cattura sicura del booleano da AJAX FormData
        $validated['rinnovo_automatico'] = $request->boolean('rinnovo_automatico');

        // GESTIONE JSON NOTIFICHE
        // Se Alpine invia la stringa "null" (Impostazioni Globali), salviamo null vero nel DB.
        // Se invia un JSON (Regole Custom), lo decodifichiamo in un array PHP.
        if ($request->notification_override && $request->notification_override !== 'null') {
            $validated['notification_override'] = json_decode($request->notification_override, true);
        } else {
            $validated['notification_override'] = null;
        }

        Subscription::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Spesa salvata con successo!'], 201);
        }
        return redirect()->route('subscriptions.index')->with('success', 'Spesa salvata!');
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'data_scadenza' => 'required|date',
            'importo' => 'nullable|numeric|min:0',
            'giorni_preavviso' => 'nullable|integer|min:0',
            'url_gestione' => 'nullable|url',
            'note' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'notification_override' => 'nullable|string',
        ]);

        // Cattura sicura del booleano da AJAX FormData
        $validated['rinnovo_automatico'] = $request->boolean('rinnovo_automatico');

        // GESTIONE JSON NOTIFICHE
        // Se Alpine invia la stringa "null" (Impostazioni Globali), salviamo null vero nel DB.
        // Se invia un JSON (Regole Custom), lo decodifichiamo in un array PHP.
        if ($request->notification_override && $request->notification_override !== 'null') {
            $validated['notification_override'] = json_decode($request->notification_override, true);
        } else {
            $validated['notification_override'] = null;
        }

        $subscription->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Spesa aggiornata con successo!']);
        }
        return redirect()->route('subscriptions.index')->with('success', 'Spesa aggiornata!');
    }

    public function destroy(Request $request, Subscription $subscription)
    {
        $subscription->delete();
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Spesa eliminata con successo!']);
        }
        return redirect()->route('subscriptions.index')->with('success', 'Spesa eliminata!');
    }
}