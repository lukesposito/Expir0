<?php

namespace App\Mail;

use App\Models\Deadline;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeadlineReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $deadline;
    public $step; // Può essere: 'year', '30', '15', '7'

    public function __construct(Deadline $deadline, $step)
    {
        $this->deadline = $deadline;
        $this->step = $step;
    }

    public function envelope(): Envelope
    {
        $oggetto = '';
        
        if ($this->step === 'year') {
            $oggetto = "📅 Promemoria: Il tuo documento scade quest'anno!";
        } elseif ($this->step === '30') {
            $oggetto = "🔔 Scadenza a 30 giorni: " . ucfirst($this->deadline->tipo);
        } elseif ($this->step === '15') {
            $oggetto = "⚠️ Scadenza a 15 giorni: " . ucfirst($this->deadline->tipo);
        } elseif ($this->step === '7') {
            $oggetto = "🚨 URGENTE: Scadenza imminente (7 giorni) per " . ucfirst($this->deadline->tipo);
        }

        return new Envelope(subject: $oggetto);
    }

    public function content(): Content
    {
        // Creerai una vista resources/views/emails/deadline_reminder.blade.php
        return new Content(view: 'emails.deadline-reminder');
    }
}