<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Vehicle;
use App\Models\ActionProposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\Documents\DocumentIngestionService;


class DocumentController extends Controller
{
    // Caricamento con associazione
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'user_id' => 'nullable|exists:users,id',
                'proposal_id' => 'nullable|integer|exists:action_proposals,id',
                'categoria' => 'required|string|in:bollo,assicurazione,revisione,patente,carta_identita,tesserino_sanitario,altro',
            ]);

            $personalCategories = [
                'patente',
                'carta_identita',
                'tesserino_sanitario',
            ];

            $isPersonalDocument = in_array(
                $validated['categoria'],
                $personalCategories,
                true
            );

            /*
            * Un documento personale deve avere una persona associata.
            * Un documento veicolo deve avere un veicolo associato.
            */
            if ($isPersonalDocument && empty($validated['user_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seleziona la persona a cui appartiene il documento.',
                ], 422);
            }

            if (!$isPersonalDocument && empty($validated['vehicle_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seleziona il veicolo a cui associare il documento.',
                ], 422);
            }

            /*
            * Se arriva un proposal_id, verifichiamo che la proposta sia ancora
            * pending e che il caricamento corrisponda esattamente al suo contesto.
            *
            * Questo impedisce che una proposta per Marco venga chiusa caricando
            * per errore una carta d'identità di Luca.
            */
            $proposal = null;

            if (!empty($validated['proposal_id'])) {
                $proposal = ActionProposal::query()
                    ->whereKey($validated['proposal_id'])
                    ->where('status', 'pending')
                    ->first();

                if (!$proposal) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La proposta non è più disponibile o è già stata gestita.',
                    ], 422);
                }

                $proposalCategory = data_get($proposal->payload, 'categoria');

                if ($proposalCategory !== $validated['categoria']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La categoria del file non corrisponde alla proposta selezionata.',
                    ], 422);
                }

                if ($proposal->action_type === 'archive_personal_document') {
                    $proposalUserId = (int) data_get($proposal->payload, 'user_id');

                    if ($proposalUserId !== (int) $validated['user_id']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'La persona selezionata non corrisponde alla proposta.',
                        ], 422);
                    }
                }

                if ($proposal->action_type === 'archive_document') {
                    $proposalVehicleId = (int) data_get($proposal->payload, 'vehicle_id');

                    if ($proposalVehicleId !== (int) $validated['vehicle_id']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Il veicolo selezionato non corrisponde alla proposta.',
                        ], 422);
                    }
                }
            }

            $file = $request->file('documento');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'public');

            $document = Document::create([
                'user_id' => Auth::id(),
                'vehicle_id' => $isPersonalDocument
                    ? null
                    : $validated['vehicle_id'],
                'associated_user_id' => $isPersonalDocument
                    ? $validated['user_id']
                    : null,
                'categoria' => $validated['categoria'],
                'path' => $path,
                'nome_originale' => $file->getClientOriginalName(),
                'tipo_file' => $file->getClientMimeType(),
            ]);

            /*
            * La proposta viene chiusa esclusivamente dopo la creazione
            * effettiva del record Document.
            */
            if ($proposal) {
                $proposal->update([
                    'status' => 'applied',
                    'resolved_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Documento archiviato correttamente.',
                'document_id' => $document->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Controlla i dati inseriti.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Non è stato possibile archiviare il documento.',
            ], 500);
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