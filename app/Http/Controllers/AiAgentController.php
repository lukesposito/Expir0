<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Auth;
use App\Models\Vehicle;
use App\Models\Deadline;
use Carbon\Carbon;

class AiAgentController extends Controller
{
    public function ask(Request $request, GeminiService $gemini)
    {
        // 1. Validazione base: la domanda non può superare i 500 caratteri
        $request->validate([
            'prompt' => 'required|string|max:500'
        ]);

        $user = Auth::user();
        $userQuestion = $request->input('prompt');

        // FASE 1: RAG (Recupero dei dati reali dal Database)        
        // Recupero i veicoli
        $veicoli = Vehicle::all();
        $testoVeicoli = "VEICOLI IN GARAGE:\n";
        if ($veicoli->isEmpty()) {
            $testoVeicoli .= "- Nessun veicolo registrato.\n";
        } else {
            foreach ($veicoli as $v) {
                $testoVeicoli .= "- {$v->marca} {$v->modello} (Targa: {$v->targa})\n";
            }
        }

        // Recupero le scadenze NON PAGATE dal database
        $scadenze = Deadline::with(['vehicle', 'user', 'subscription'])
            ->where('pagato', false)
            ->orderBy('data_scadenza', 'asc')
            ->get();

        $testoScadenze = "SCADENZE IN SOSPESO / DA PAGARE:\n";
        if ($scadenze->isEmpty()) {
            $testoScadenze .= "- Nessuna scadenza in sospeso.\n";
        } else {
            foreach ($scadenze as $s) {
                $data = Carbon::parse($s->data_scadenza)->format('d/m/Y');
                
                // Capire a chi appartiene la spesa
                $soggetto = 'Spesa generica';
                if ($s->tipo === 'spesa_abbonamento' && $s->subscription) {
                    $soggetto = "Abbonamento " . $s->subscription->nome;
                } elseif ($s->vehicle) {
                    $soggetto = "Veicolo " . strtoupper($s->vehicle->marca . ' ' . $s->vehicle->modello);
                } elseif ($s->user) {
                    $soggetto = "Utente " . $s->user->name;
                }

                $tipo = ucfirst(str_replace('_', ' ', $s->tipo));
                $importo = number_format($s->importo ?? 0, 2, ',', '.');
                
                // Formattazione chiara per l'AI
                $testoScadenze .= "- {$tipo} per {$soggetto} | Scade il: {$data} | Importo: € {$importo}\n";
            }
        }

        // 2. IL SYSTEM PROMPT (Le Istruzioni e i Guardrail alla "Microsoft Copilot")
        $systemPrompt = "Sei l'Assistente AI ufficiale del gestionale scadenze e veicoli della famiglia. Oltre a ricordare le scadenze, sei anche un CONSULENTE DI RISPARMIO ESPERTO e proattivo.
        
        CONTESTO E DATI REALI DELL'UTENTE (USA SOLO QUESTI DATI PER RISPONDERE SULLE SCADENZE):
        {$testoVeicoli}
        {$testoScadenze}
        
        REGOLE E GUARDRAIL OBBLIGATORI:
        1. Il tuo tono deve essere professionale, amichevole, chiaro e rassicurante. Usa gli elenchi puntati per facilitare la lettura.
        2. LETTURA DATI: Se l'utente chiede delle scadenze, rispondi elencando esattamente le date e gli importi presenti nei dati forniti.
        3. CONSIGLI DI RISPARMIO: Se l'utente chiede come risparmiare, analizza le scadenze fornite (es. assicurazioni, bolli, abbonamenti). Offri 2 o 3 consigli pratici basati sui dati reali. Ad esempio: 
           - Per le Assicurazioni RCA, consiglia di usare comparatori online 30 giorni prima della scadenza o valutare la scatola nera.
           - Per le Revisioni, consiglia un pre-check dal meccanico di fiducia per evitare di dover pagare due volte in caso di esito negativo.
           - Per gli Abbonamenti, invita l'utente a valutare se li sta realmente utilizzando.
           - Se ci sono molte spese concentrate in un solo mese, faglielo notare e suggerisci di accantonare un budget in anticipo.
        4. Se l'utente chiede una scadenza che NON è presente nell'elenco fornito, rispondi semplicemente che non la trovi registrata.
        5. COSA NON FARE: Se la domanda esce dal perimetro di veicoli, documenti, scadenze e risparmio su queste voci, declina gentilmente la risposta.
        
        DOMANDA DELL'UTENTE ({$user->name}):
        '{$userQuestion}'";

        // 3. Chiamata sicura all'AI
        $risposta = $gemini->ask($systemPrompt);

        // Ritorno la risposta al frontend
        return response()->json([
            'status' => 'success',
            'response' => $risposta
        ]);
    }

    public function analyze(Request $request, GeminiService $gemini)
    {
        $year = $request->input('year', date('Y'));
        
        // 1. Recupero dati aggregati per l'analisi
        $veicoliCount = Vehicle::count();
        $scadenzeAnno = Deadline::whereYear('data_scadenza', $year)->get();
        
        $speso = $scadenzeAnno->where('pagato', true)->sum('importo');
        $daPagare = $scadenzeAnno->where('pagato', false)->sum('importo');
        
        $critiche = $scadenzeAnno->where('pagato', false)
            ->where('data_scadenza', '<=', now()->addDays(30))
            ->map(fn($s) => "- {$s->tipo}: €" . number_format($s->importo, 2, ',', '.'))
            ->implode("\n");

        // 2. Prepariamo il prompt per l'analista
        $prompt = "Agisci come un Analista Finanziario esperto di gestione Automotive e Spese Familiari. 
        Analizza i seguenti dati reali dell'utente per l'anno {$year}:
        - Veicoli totali in gestione: {$veicoliCount}
        - Totale già pagato quest'anno: € " . number_format($speso, 2, ',', '.') . "
        - Totale ancora da pagare (impegni futuri): € " . number_format($daPagare, 2, ',', '.') . "
        - Scadenze critiche nei prossimi 30 giorni:\n" . ($critiche ?: "Nessuna") . "

        REQUISITI DEL REPORT:
        - Dai un titolo professionale (es. 'Analisi Strategica Portafoglio {$year}').
        - Valuta la salute finanziaria dell'utente (è in linea con le spese o ci sono picchi?).
        - Fornisci 3 consigli 'Executive' per ottimizzare i costi di questi veicoli.
        - Usa il Markdown (grassetto, liste puntate) per rendere il report leggibile ed elegante.";

        $analisi = $gemini->ask($prompt);

        return response()->json([
            'status' => 'success',
            'analysis' => $analisi
        ]);
    }
}