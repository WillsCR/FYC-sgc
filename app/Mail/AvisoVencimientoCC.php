<?php

namespace App\Mail;

use App\Models\CertCalidad;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AvisoVencimientoCC extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CertCalidad $cert) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Aviso de vencimiento — ' . $this->cert->descripcion,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aviso_vencimiento_cc',
            with: ['cert' => $this->cert],
        );
    }
}
