<?php

namespace App\Console\Commands;

use App\Mail\DeadlineReminder;
use App\Models\Deadline;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckDeadlines extends Command
{
    protected $signature = 'deadlines:check';
    protected $description = 'Controlla scadenze imminenti e invia notifiche a tutti gli utenti';

    public function handle()
    {
        $oggi = Carbon::now();
        
        // Notifiche a 30 giorni
        $scadenze30 = Deadline::where('data_scadenza', $oggi->copy()->addDays(30)->format('Y-m-d'))
            ->where('pagato', false)
            ->get();
        
        foreach ($scadenze30 as $scadenza) {
            Mail::to($scadenza->user->email ?? 'default@example.com')
                ->send(new ScadenzaReminder($scadenza, 30));
        }
        
        // Notifiche a 7 giorni
        $scadenze7 = Deadline::where('data_scadenza', $oggi->copy()->addDays(7)->format('Y-m-d'))
            ->where('pagato', false)
            ->get();
        
        foreach ($scadenze7 as $scadenza) {
            Mail::to($scadenza->user->email ?? 'default@example.com')
                ->send(new ScadenzaUrgente($scadenza, 7));
        }
        
        // Notifiche scadenze superate
        $scaduteOggi = Deadline::where('data_scadenza', $oggi->copy()->subDay()->format('Y-m-d'))
            ->where('pagato', false)
            ->get();
        
        foreach ($scaduteOggi as $scadenza) {
            Mail::to($scadenza->user->email ?? 'default@example.com')
                ->send(new ScadenzaSuperata($scadenza));
        }
    }

    // public function handle()
    // {
    //     // Prendi tutte le scadenze tra oggi e 10 giorni
    //     $deadlines = Deadline::where('data_scadenza', '<=', now()->addDays(10))
    //         ->where('data_scadenza', '>=', now())
    //         ->where('notifica_inviata', false)
    //         ->with('vehicle')
    //         ->get();

    //     if ($deadlines->isEmpty()) {
    //         $this->info("Nessuna scadenza imminente da notificare.");
    //         return;
    //     }

    //     // Prendi tutti gli utenti registrati
    //     $users = User::all();

    //     if ($users->isEmpty()) {
    //         $this->warn("Nessun utente registrato nel sistema.");
    //         return;
    //     }

    //    foreach ($deadlines as $deadline) {
    //         if ($deadline->tipo === 'patente') {
    //             $subject = "Patente di {$deadline->user->name}";
    //         } else {
    //             $subject = "{$deadline->vehicle->targa}";
    //         }
            
    //         foreach ($users as $user) {
    //             Mail::to($user->email)->send(new DeadlineReminder($deadline));
    //             $this->info("Notifica inviata a {$user->email} per: {$deadline->tipo} - {$subject}");
    //         }
            
    //         $deadline->update(['notifica_inviata' => true]);
    //     }

    //     $this->info("Totale scadenze notificate: {$deadlines->count()}");
    //     $this->info("Totale utenti notificati: {$users->count()}");
    // }
}