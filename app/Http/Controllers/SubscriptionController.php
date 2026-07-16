<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Deadline;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
                    'renewal_interval_months' => (int) ($sub->renewal_interval_months ?? 1),
                    'renewal_notification_channels' => $sub->renewal_notification_channels ?? [],
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
            'data_scadenza' => ['nullable','date', Rule::requiredIf(!$request->boolean('rinnovo_automatico')),],
            'importo' => 'nullable|numeric|min:0',
            'giorni_preavviso' => 'nullable|integer|min:0',
            'url_gestione' => 'nullable|url',
            'note' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'notification_override' => 'nullable|string',
            'renewal_interval_months' => 'nullable|integer|min:1|max:12',
            'renewal_notification_channels' => 'nullable|string',
        ]);

        // Cattura sicura del booleano da AJAX FormData
        $validated['rinnovo_automatico'] = $request->boolean('rinnovo_automatico');

        // Se il rinnovo automatico è attivo e la data è vuota,
        // usiamo la data odierna come prima scadenza/base di rinnovo.
        if ($validated['rinnovo_automatico'] && empty($validated['data_scadenza'])) {
            $validated['data_scadenza'] = now()->toDateString();
        }

        // Intervallo rinnovo: valido solo se il rinnovo automatico è attivo
        $validated['renewal_interval_months'] = $validated['rinnovo_automatico']
            ? (int) $request->input('renewal_interval_months', 1)
            : null;

        // Canali notifica rinnovo
        $validated['renewal_notification_channels'] = $request->filled('renewal_notification_channels')
            ? json_decode($request->input('renewal_notification_channels'), true)
            : [];

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
            'data_scadenza' => ['nullable','date', Rule::requiredIf(!$request->boolean('rinnovo_automatico')),],
            'importo' => 'nullable|numeric|min:0',
            'giorni_preavviso' => 'nullable|integer|min:0',
            'url_gestione' => 'nullable|url',
            'note' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'notification_override' => 'nullable|string',
            'renewal_interval_months' => 'nullable|integer|min:1|max:12',
            'renewal_notification_channels' => 'nullable|string',
        ]);

        // Cattura sicura del booleano da AJAX FormData
        $validated['rinnovo_automatico'] = $request->boolean('rinnovo_automatico');

        // Se il rinnovo automatico è attivo e la data è vuota,
        // usiamo la data odierna come prima scadenza/base di rinnovo.
        if ($validated['rinnovo_automatico'] && empty($validated['data_scadenza'])) {
            $validated['data_scadenza'] = now()->toDateString();
        }

        // Intervallo rinnovo: valido solo se il rinnovo automatico è attivo
        $validated['renewal_interval_months'] = $validated['rinnovo_automatico']
            ? (int) $request->input('renewal_interval_months', 1)
            : null;

        // Canali notifica rinnovo
        $validated['renewal_notification_channels'] = $request->filled('renewal_notification_channels')
            ? json_decode($request->input('renewal_notification_channels'), true)
            : [];


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
        Deadline::where('subscription_id', $subscription->id)
            ->where('tipo', 'spesa_abbonamento')
            ->delete();

        $subscription->delete();
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Spesa eliminata con successo!']);
        }
        return redirect()->route('subscriptions.index')->with('success', 'Spesa eliminata!');
    }
}