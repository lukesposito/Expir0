<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // Caricamento con associazione
    public function store(Request $request)
    {
        $request->validate([
            'documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
            'vehicle_id' => 'nullable|exists:vehicles,id', // Null se è patente
            'categoria' => 'required|string|in:bollo,assicurazione,revisione,patente,altro',
        ]);

        if ($request->hasFile('documento')) {
            $file = $request->file('documento');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Salva nel disco public
            $path = $file->storeAs('documents', $filename, 'public');

            Document::create([
                'user_id' => Auth::id(),
                'vehicle_id' => $request->vehicle_id, // Può essere null per Patente
                'categoria' => $request->categoria,
                'path' => $path,
                'nome_originale' => $file->getClientOriginalName(),
                'tipo_file' => $file->getClientMimeType(),
            ]);

            return redirect()->back()->with('success', 'Documento archiviato correttamente.');
        }

        return redirect()->back()->with('error', 'Errore nel caricamento.');
    }

    // Eliminazione
    public function destroy(Document $document)
    {
        // Controllo che il documento appartenga all'utente (sicurezza)
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        // 1. Elimina file fisico
        if (Storage::disk('public')->exists($document->path)) {
            Storage::disk('public')->delete($document->path);
        }

        // 2. Elimina record DB
        $document->delete();

        return redirect()->back()->with('success', 'Documento eliminato definitivamente.');
    }
}