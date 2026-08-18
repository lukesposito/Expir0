<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\Documents\DocumentIngestionService;

class DocumentController extends Controller
{
    // Caricamento con associazione
    public function store(Request $request, DocumentIngestionService $ingestionService)
    {
        try {
            $validated = $request->validate([
                'documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'user_id' => 'nullable|exists:users,id',
                'categoria' => 'required|string|in:bollo,assicurazione,revisione,patente,carta_identita,tesserino_sanitario,altro',
            ]);

            if ($request->hasFile('documento')) {
                $file = $request->file('documento');
                $ingestionService->ingest($validated, $file);

                return response()->json([
                    'success' => true,
                    'message' => 'Documento archiviato correttamente.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Errore nel caricamento del file.',
                ], 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore: ' . $e->getMessage()
            ], 422);
        }
    }

    // Eliminazione
    public function destroy(Document $document)
    {
        // Controllo che il documento appartenga all'utente (sicurezza)
        if ($document->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorizzato a eliminare questo documento.'
            ], 403);
        }

        // 1. Elimina file fisico
        if (Storage::disk('public')->exists($document->path)) {
            Storage::disk('public')->delete($document->path);
        }

        // 2. Elimina record DB
        $document->delete();

        // 3. Rispondo con JSON invece del redirect!
        return response()->json([
            'success' => true,
            'message' => 'Documento eliminato definitivamente.'
        ]);
    }
}