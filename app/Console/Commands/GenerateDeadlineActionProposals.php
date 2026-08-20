<?php

namespace App\Console\Commands;

use App\Models\ActionProposal;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\Vehicle;
use App\Models\Patente;
use App\Models\CartaIdentita;
use App\Models\TesserinoSanitario;
use Illuminate\Console\Command;

class GenerateDeadlineActionProposals extends Command
{
    protected $signature = 'app:generate-deadline-action-proposals';

    protected $description = 'Genera proposte operative per scadenze non pagate e scadute';

    public function handle(): int
    {        
        /*
        |--------------------------------------------------------------------------
        | 1. Scadenze già superate e non pagate
        |--------------------------------------------------------------------------
        |
        | Proposta operativa: l'utente può confermare il pagamento,
        | con o senza creazione del rinnovo successivo.
        |
        */

        $created = 0;

        $deadlines = Deadline::query()
            ->where('pagato', false)
            ->whereDate('data_scadenza', '<', today())
            ->get();

        foreach ($deadlines as $deadline) {
            $alreadyExists = ActionProposal::query()
                ->where('status', 'pending')
                ->where('action_type', 'mark_paid')
                ->where('source_type', 'deadline')
                ->where('source_id', $deadline->id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            ActionProposal::create([
                'user_id' => $deadline->user_id,
                'status' => 'pending',
                'action_type' => 'mark_paid',
                'source_type' => 'deadline',
                'source_id' => $deadline->id,
                'target_type' => 'deadline',
                'target_id' => $deadline->id,
                'payload' => [
                    'deadline_id' => $deadline->id,
                    'tipo' => $deadline->tipo,
                    'data_scadenza' => $deadline->data_scadenza?->format('Y-m-d'),
                    'importo' => $deadline->importo,
                    'suggest_renewal' => true,
                ],
                'confidence' => 100,
                'reason' => 'La scadenza risulta superata e non ancora segnata come pagata.',
            ]);

            $created++;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Scadenze in arrivo nei prossimi 7 giorni
        |--------------------------------------------------------------------------
        |
        | Proposta preventiva: non modifica nulla e porta solo l'utente
        | alla gestione della scadenza prima della data limite.
        |
        */

        $imminenti = Deadline::query()
            ->where('pagato', false)
            ->whereDate('data_scadenza', '>=', today())
            ->whereDate('data_scadenza', '<=', today()->copy()->addDays(7))
            ->get();

        foreach ($imminenti as $deadline) {
            $alreadyExists = ActionProposal::query()
                ->where('status', 'pending')
                ->where('action_type', 'upcoming_deadline')
                ->where('source_type', 'deadline')
                ->where('source_id', $deadline->id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $giorniRimanenti = today()->diffInDays(
                $deadline->data_scadenza->copy()->startOfDay(),
                false
            );

            $messaggio = $giorniRimanenti === 0
                ? 'La scadenza è prevista per oggi.'
                : 'La scadenza è prevista tra ' . $giorniRimanenti . ' giorni.';

            ActionProposal::create([
                'user_id' => $deadline->user_id,
                'status' => 'pending',
                'action_type' => 'upcoming_deadline',
                'source_type' => 'deadline',
                'source_id' => $deadline->id,
                'target_type' => 'deadline',
                'target_id' => $deadline->id,
                'payload' => [
                    'deadline_id' => $deadline->id,
                    'tipo' => $deadline->tipo,
                    'data_scadenza' => $deadline->data_scadenza?->format('Y-m-d'),
                    'importo' => $deadline->importo,
                    'days_remaining' => $giorniRimanenti,
                    'suggest_renewal' => false,
                ],
                'confidence' => 100,
                'reason' => $messaggio,
            ]);

            $created++;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Spese importanti in arrivo entro 30 giorni
        |--------------------------------------------------------------------------
        |
        | Proposta preventiva per le scadenze non pagate con un importo
        | significativo. Non modifica la deadline: invita l'utente a pianificare
        | o gestire in anticipo una spesa rilevante.
        |
        */

        $sogliaImporto = 300;

        $speseImportanti = Deadline::query()
            ->where('pagato', false)
            ->whereNotNull('importo')
            ->where('importo', '>=', $sogliaImporto)
            ->whereDate('data_scadenza', '>=', today()->copy()->addDays(8))
            ->whereDate('data_scadenza', '<=', today()->copy()->addDays(30))
            ->get();

        foreach ($speseImportanti as $deadline) {
            $alreadyExists = ActionProposal::query()
                ->where('status', 'pending')
                ->where('action_type', 'high_value_upcoming')
                ->where('source_type', 'deadline')
                ->where('source_id', $deadline->id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $giorniRimanenti = today()->diffInDays(
                $deadline->data_scadenza->copy()->startOfDay(),
                false
            );

            ActionProposal::create([
                'user_id' => $deadline->user_id,
                'status' => 'pending',
                'action_type' => 'high_value_upcoming',
                'source_type' => 'deadline',
                'source_id' => $deadline->id,
                'target_type' => 'deadline',
                'target_id' => $deadline->id,
                'payload' => [
                    'deadline_id' => $deadline->id,
                    'tipo' => $deadline->tipo,
                    'data_scadenza' => $deadline->data_scadenza?->format('Y-m-d'),
                    'importo' => $deadline->importo,
                    'days_remaining' => $giorniRimanenti,
                    'threshold_amount' => $sogliaImporto,
                    'suggest_renewal' => false,
                ],
                'confidence' => 100,
                'reason' => 'Spesa importante prevista tra '
                    . $giorniRimanenti
                    . ' giorni: €'
                    . number_format($deadline->importo, 2, ',', '.')
                    . '.',
            ]);

            $created++;
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Documenti consigliati per i veicoli
        |--------------------------------------------------------------------------
        |
        | Il sistema non dichiara che il documento non esiste: controlla solo se
        | non risulta archiviato nella piattaforma per il veicolo e la categoria.
        |
        */

        $vehicleDocumentTypes = [
            'bollo' => [
                'label' => 'Ricevuta bollo',
                'description' => 'Non risulta una ricevuta del bollo archiviata per questo veicolo.',
            ],
            'assicurazione' => [
                'label' => 'Polizza assicurativa',
                'description' => 'Non risulta una polizza assicurativa archiviata per questo veicolo.',
            ],
            'revisione' => [
                'label' => 'Documento revisione',
                'description' => 'Non risulta un documento di revisione archiviato per questo veicolo.',
            ],
        ];

        $vehicles = Vehicle::query()
            ->with('user')
            ->get();

        foreach ($vehicles as $vehicle) {
            foreach ($vehicleDocumentTypes as $category => $documentInfo) {
                $documentAlreadyArchived = Document::query()
                    ->where('vehicle_id', $vehicle->id)
                    ->where('categoria', $category)
                    ->exists();

                if ($documentAlreadyArchived) {
                    continue;
                }

                $alreadyExists = ActionProposal::query()
                    ->where('status', 'pending')
                    ->where('action_type', 'archive_document')
                    ->where('source_type', 'vehicle')
                    ->where('source_id', $vehicle->id)
                    ->where('target_type', 'document')
                    ->whereJsonContains('payload->categoria', $category)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                ActionProposal::create([
                    'user_id' => $vehicle->user_id,
                    'status' => 'pending',
                    'action_type' => 'archive_document',
                    'source_type' => 'vehicle',
                    'source_id' => $vehicle->id,
                    'target_type' => 'document',
                    'target_id' => null,
                    'payload' => [
                        'vehicle_id' => $vehicle->id,
                        'vehicle_targa' => $vehicle->targa,
                        'vehicle_label' => trim(
                            ($vehicle->marca ?? '')
                            . ' '
                            . ($vehicle->modello ?? '')
                        ),
                        'categoria' => $category,
                        'document_label' => $documentInfo['label'],
                    ],
                    'confidence' => 100,
                    'reason' => $documentInfo['description'],
                ]);

                $created++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Documenti personali consigliati
        |--------------------------------------------------------------------------
        |
        | Una proposta rappresenta una persona + una categoria documentale.
        | Non un singolo record di Patente, CartaIdentita o TesserinoSanitario.
        |
        | Esempio:
        | Marco + tessera sanitaria = massimo una proposta pending.
        |
        */

        $personalDocumentSources = [
            [
                'model' => Patente::class,
                'category' => 'patente',
                'label' => 'Patente',
                'description' => 'Non risulta una patente archiviata per questa persona.',
            ],
            [
                'model' => CartaIdentita::class,
                'category' => 'carta_identita',
                'label' => 'Carta d’identità',
                'description' => 'Non risulta una carta d’identità archiviata per questa persona.',
            ],
            [
                'model' => TesserinoSanitario::class,
                'category' => 'tesserino_sanitario',
                'label' => 'Tessera sanitaria',
                'description' => 'Non risulta una tessera sanitaria archiviata per questa persona.',
            ],
        ];

        foreach ($personalDocumentSources as $sourceConfig) {
            /*
            * Prendiamo una sola volta ogni persona che possiede almeno
            * un record del tipo attuale. unique() evita i duplicati:
            *
            * Marco con 3 record tessera sanitaria
            * -> Marco viene controllato una sola volta.
            */
            $userIds = $sourceConfig['model']::query()
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->unique()
                ->values();

            foreach ($userIds as $userId) {
                $person = \App\Models\User::find($userId);

                if (!$person) {
                    continue;
                }

                /*
                * Il documento personale viene associato alla persona tramite
                * associated_user_id, NON tramite documents.user_id.
                *
                * documents.user_id = chi ha effettuato materialmente l'upload.
                * documents.associated_user_id = persona a cui appartiene il documento.
                */
                $documentAlreadyArchived = Document::query()
                    ->where('associated_user_id', $userId)
                    ->where('categoria', $sourceConfig['category'])
                    ->exists();

                /*
                * Cerchiamo eventuali proposte ancora aperte per la stessa:
                * persona + categoria.
                *
                * source_type e source_id usano una chiave stabile:
                * personal_document + ID persona.
                */
                $pendingProposalsQuery = ActionProposal::query()
                    ->where('status', 'pending')
                    ->where('action_type', 'archive_personal_document')
                    ->where('source_type', 'personal_document')
                    ->where('source_id', $userId)
                    ->where('target_type', 'document')
                    ->whereJsonContains(
                        'payload->categoria',
                        $sourceConfig['category']
                    );

                /*
                * Se il documento ora esiste, qualsiasi proposta pending precedente
                * per quella persona/categoria è diventata obsoleta.
                *
                * Per ora usiamo rejected per evitare una migration aggiuntiva.
                * Non significa che l'utente l'abbia ignorata: significa soltanto
                * che non deve più apparire nel Centro Azioni.
                */
                if ($documentAlreadyArchived) {
                    $pendingProposalsQuery->update([
                        'status' => 'rejected',
                        'resolved_at' => now(),
                    ]);

                    continue;
                }

                /*
                * Documento assente e nessuna proposta pending:
                * creiamo una sola proposta.
                */
                if ($pendingProposalsQuery->exists()) {
                    continue;
                }

                ActionProposal::create([
                    'user_id' => $userId,
                    'status' => 'pending',
                    'action_type' => 'archive_personal_document',

                    /*
                    * La source non è più il singolo record personale:
                    * è la persona a cui manca un documento di questa categoria.
                    */
                    'source_type' => 'personal_document',
                    'source_id' => $userId,

                    'target_type' => 'document',
                    'target_id' => null,

                    'payload' => [
                        'user_id' => $userId,
                        'user_name' => $person->name,
                        'categoria' => $sourceConfig['category'],
                        'document_label' => $sourceConfig['label'],
                    ],

                    'confidence' => 100,
                    'reason' => $sourceConfig['description'],
                ]);

                $created++;
            }
        }

        $this->info("Generate {$created} proposte operative.");

        return self::SUCCESS;
    }
}