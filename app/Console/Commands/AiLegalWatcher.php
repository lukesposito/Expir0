<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeminiService;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AiLegalWatcher extends Command
{
    // Il nome del comando da terminale
    protected $signature = 'app:ai-legal-watcher';
    protected $description = 'Interroga Gemini per novità legali e avvisa gli utenti (Agentic AI)';

    public function handle(GeminiService $gemini)
    {
        $this->info("=======================================");
        $this->info("🤖 Avvio Osservatorio AI Burocratico...");
        $this->info("=======================================\n");

        $oggi = Carbon::now()->format('d/m/Y');

        $prompt = "Sei un esperto consulente legale e burocratico italiano. Oggi è il $oggi.
        Il tuo compito è ricercare e riassumere in modo chiaro e preciso ESCLUSIVAMENTE le NOVITÀ, le PROROGHE o i DECRETI LEGGE approvati o entrati in vigore in Italia negli ULTIMI 10 GIORNI riguardanti questi argomenti:
        - Patenti di guida
        - Revisioni e Bollo auto
        - Assicurazioni RCA
        - Carta d'Identità e Passaporti
        - Passaggio obbligatorio alla Carta d'Identità Elettronica (CIE) o novità Passaporti.
        
        // REGOLE FONDAMENTALI:
        // 1. Se NON ci sono novità rilevanti o notizie confermate negli ultimi 10 giorni, devi rispondere ESATTAMENTE con una sola parola: NESSUNA_NOVITA. Non aggiungere altro testo.
        // 2. Se ci sono novità, formatta la risposta con elenchi puntati chiari, indicando cosa cambia e per chi.";

        $this->line("🧠 Sto interrogando Google Gemini...");
        
        // Eseguiamo la chiamata all'AI
        $risposta = $gemini->ask($prompt);

        // IMPOSTA QUI LA TUA EMAIL DA AMMINISTRATORE
        $adminEmail = 'lukeesposito@outlook.it'; 

        // Controllo Anti-Spam e Gestione Risposta
        if (trim($risposta) === 'NESSUNA_NOVITA') {
            $this->info("\n Controllo terminato: Nessuna novità rilevante. Preparo l'email di 'Tutto tranquillo' SOLO PER L'ADMIN.");
            
            // Testo rassicurante da parte dell'Admin
            $risposta = "Abbiamo monitorato con attenzione le direttive ministeriali e le novità legislative degli ultimi 10 giorni.\n\nAl momento la situazione è tranquilla: non ci sono nuove leggi, proroghe o scadenze urgenti da segnalare per i tuoi veicoli o i tuoi documenti.\n\nContinueremo a tenere d'occhio la situazione per te, in modo da tenerti sempre aggiornato ed evitare brutte sorprese!";
            $oggettoEmail = 'Aggiornamento Normative: Nessuna nuova legge questa settimana';
            
            // FILTRO: Seleziono SOLO il tuo utente
            $utentiValidi = User::where('email', $adminEmail)->get();
            
        } else {
            $this->info("\nTrovate novità legali! Preparo l'invio delle comunicazioni a TUTTI GLI UTENTI...");
            $oggettoEmail = 'Allerta Normative: Importanti Novità per i tuoi Documenti';
            
            // FILTRO: Seleziono tutti gli utenti attivi della famiglia
            $utentiValidi = User::where('is_active', true)
                                ->where('receives_notifications', true)
                                ->get();
        }

        $emailInviate = 0;

        foreach ($utentiValidi as $user) {
            if (!empty($user->email)) {
                // Intro ed Outro firmate "L'Amministrazione"
                Mail::raw("Ciao {$user->name},\n\nTi inviamo l'ultimo bollettino di aggiornamento curato dalla nostra amministrazione riguardo le normative italiane su veicoli e documenti:\n\n" . $risposta . "\n\nBuona settimana e al prossimo aggiornamento!\nL'Amministrazione", function($msg) use ($user, $oggettoEmail) {
                    $msg->to($user->email)
                        ->subject($oggettoEmail);
                });
                
                $this->line("   -> 📧 Email inviata a: {$user->email}");
                $emailInviate++;
            }
        }
        
        $this->info("\n=======================================");
        $this->info("Operazione completata. Inviati {$emailInviate} bollettini.");
    }
}