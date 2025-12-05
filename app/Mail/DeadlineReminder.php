<?php

namespace App\Mail;

use App\Models\Deadline;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeadlineReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Deadline $deadline)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Scadenza ' . ucfirst($this->deadline->tipo) . ' imminente',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.deadline-reminder',
        );
    }
}