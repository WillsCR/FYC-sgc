<?php

namespace App\Mail;

use App\Models\MatrizCurso;
use App\Models\MatrizTrabajador;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AvisoVencimientoMC extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MatrizCurso $curso,
        public MatrizTrabajador $trabajador
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Aviso de vencimiento — ' . $this->curso->curso,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aviso_vencimiento_mc',
            with: [
                'curso'      => $this->curso,
                'trabajador' => $this->trabajador,
            ],
        );
    }
}
