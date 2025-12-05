<?php

namespace App\Http\Controllers;

use App\Models\Patente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatenteController extends Controller
{
    public function index()
    {
        $patenti = Patente::where('user_id', Auth::id())
            ->with('user')  // Carica la relazione user
            ->withCount('deadlines')
            ->get()  // Usa get() invece di paginate() per Alpine.js
            ->map(function($patente) {
                return [
                    'id' => $patente->id,
                    'numero_patente' => $patente->numero_patente,
                    'tipo' => $patente->tipo,
                    'data_rilascio' => $patente->data_rilascio ? $patente->data_rilascio->format('Y-m-d') : null,
                    'data_rilascio_formatted' => $patente->data_rilascio ? $patente->data_rilascio->format('d/m/Y') : null,
                    'deadlines_count' => $patente->deadlines_count,
                    'user' => [
                        'id' => $patente->user->id,
                        'name' => $patente->user->name,
                        'email' => $patente->user->email,
                    ]
                ];
            });
        
        return view('patenti.index', compact('patenti'));
    }

    public function create()
    {
        return view('patenti.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_patente' => 'required|string|max:20|unique:patentes',
            'tipo' => 'nullable|string|max:10',
            'data_rilascio' => 'nullable|date',
        ]);

        $validated['user_id'] = Auth::id();

        Patente::create($validated);
        return redirect()->route('patenti.index')->with('success', 'Patente aggiunta con successo');
    }

    public function edit(Patente $patente)
    {
        // Verifica che la patente appartenga all'utente
        if ($patente->user_id !== Auth::id()) {
            abort(403);
        }
        
        return view('patenti.edit', compact('patente'));
    }

    public function update(Request $request, Patente $patente)
    {
        if ($patente->user_id !== Auth::id()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'numero_patente' => 'required|string|max:20|unique:patentes,numero_patente,' . $patente->id,
            'tipo' => 'nullable|string|max:10',
            'data_rilascio' => 'nullable|date',
        ]);

        $patente->update($validated);
        return redirect()->route('patenti.index')->with('success', 'Patente aggiornata con successo');
    }

    public function destroy(Patente $patente)
    {
        if ($patente->user_id !== Auth::id()) {
            abort(403);
        }
        
        $patente->delete();
        return redirect()->route('patenti.index')->with('success', 'Patente eliminata con successo');
    }
}