<?php

namespace App\Mail;

use App\Models\ProgramaVerificacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AvisoVencimientoCI extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ProgramaVerificacion $programa) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Aviso de vencimiento — ' . $this->programa->descripcion,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aviso_vencimiento_ci',
            with: ['programa' => $this->programa],
        );
    }
}
