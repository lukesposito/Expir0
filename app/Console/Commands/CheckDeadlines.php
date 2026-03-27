<?php

namespace App\Console\Commands;

use App\Mail\DeadlineReminder;
use App\Models\Deadline;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CheckDeadlines extends Command
{
    protected $signature = 'deadlines:check';
    protected $description = 'Controlla scadenze imminenti e invia una notifica aggregata a tutti gli utenti';

    public function handle()
    {
        // 1. Trova tutte le scadenze nei prossimi 30 giorni (Non pagate e Non ancora notificate)
        $deadlines = Deadline::with(['vehicle', 'user'])
            ->where('data_scadenza', '<=', Carbon::now()->addDays(30))
            ->where('data_scadenza', '>=', Carbon::now())
            ->where('pagato', false)
            ->where('notifica_inviata', false)
            ->get();

        if ($deadlines->isEmpty()) {
            $this->info("Nessuna nuova scadenza imminente da notificare.");
            return;
        }

        // 2. Prendi tutti gli utenti registrati
        $users = User::all();

        if ($users->isEmpty()) {
            $this->warn("Nessun utente registrato nel sistema.");
            return;
        }

        // 3. Invia UNA SINGOLA EMAIL a tutti gli utenti con l'elenco delle scadenze
        foreach ($users as $user) {
            Mail::to($user->email)->send(new DeadlineReminder($deadlines, $user));
            $this->info("Notifica inviata a {$user->email} con {$deadlines->count()} scadenze.");
        }

        // 4. Segna tutte queste scadenze come "Notificate" per non reinviarle domani!
        foreach ($deadlines as $deadline) {
            $deadline->update(['notifica_inviata' => true]);
        }

        $this->info("Elaborazione completata: Inviate notifiche per {$deadlines->count()} scadenze.");
    }
}