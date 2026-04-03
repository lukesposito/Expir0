<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with(['user', 'vehicle'])
            ->orderBy('data_scadenza', 'asc')
            ->get()
            ->map(function ($sub) {
                $scadenza = Carbon::parse($sub->data_scadenza);
                $oggi = Carbon::now()->startOfDay();
                
                return [
                    'id' => $sub->id,
                    'nome' => $sub->nome,
                    'categoria' => $sub->categoria,
                    'data_scadenza' => $sub->data_scadenza->format('Y-m-d'),
                    'data_scadenza_formatted' => $sub->data_scadenza->format('d/m/Y'),
                    'importo' => $sub->importo,
                    'importo_formatted' => $sub->importo ? number_format($sub->importo, 2, ',', '.') : '0,00',
                    'rinnovo_automatico' => $sub->rinnovo_automatico,
                    'giorni_preavviso' => $sub->giorni_preavviso,
                    'url_gestione' => $sub->url_gestione,
                    'note' => $sub->note,
                    'user_id' => $sub->user_id,
                    'user_name' => $sub->user ? $sub->user->name : 'N/D',
                    'vehicle_id' => $sub->vehicle_id,
                    'vehicle_desc' => $sub->vehicle ? $sub->vehicle->marca . ' ' . $sub->vehicle->modello . ' (' . $sub->vehicle->targa . ')' : null,
                    'giorni_rimanenti' => (int) $oggi->diffInDays($scadenza, false),
                ];
            });

        $users = User::orderBy('name')->get();
        $vehicles = Vehicle::orderBy('targa')->get();

        if (request()->wantsJson()) {
            return response()->json(['subscriptions' => $subscriptions]);
        }

        return view('subscriptions.index', compact('subscriptions', 'users', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'data_scadenza' => 'required|date',
            'importo' => 'nullable|numeric|min:0',
            'rinnovo_automatico' => 'boolean',
            'giorni_preavviso' => 'required|integer|min:0',
            'url_gestione' => 'nullable|url',
            'note' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        Subscription::create($validated);
        return response()->json(['message' => 'Spesa salvata con successo!'], 201);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'data_scadenza' => 'required|date',
            'importo' => 'nullable|numeric|min:0',
            'rinnovo_automatico' => 'boolean',
            'giorni_preavviso' => 'required|integer|min:0',
            'url_gestione' => 'nullable|url',
            'note' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        // Assicurati che i checkbox non inviati contino come false
        $validated['rinnovo_automatico'] = $request->has('rinnovo_automatico');

        $subscription->update($validated);
        return response()->json(['message' => 'Spesa aggiornata con successo!']);
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return response()->json(['message' => 'Spesa eliminata con successo!']);
    }
}
