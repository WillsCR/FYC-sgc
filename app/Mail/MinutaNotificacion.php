<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class MinutaNotificacion extends Mailable
{
    use Queueable, SerializesModels;

    public array $datos;
    public string $accion; // 'creada' | 'actualizada'

    /**
     * $datos contiene toda la info de la minuta ya formateada.
     */
    public function __construct(array $datos, string $accion = 'creada')
    {
        $this->datos  = $datos;
        $this->accion = $accion;
    }

    public function envelope(): Envelope
    {
        $verbo  = $this->accion === 'creada' ? 'Nueva' : 'Actualización de';
        $area   = $this->datos['area_nombre'];
        $fecha  = $this->datos['fecha_formateada'];

        return new Envelope(
            subject: "{$verbo} minuta — {$area} ({$fecha})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.minuta_notificacion',
            with: [
                'datos'  => $this->datos,
                'accion' => $this->accion,
            ],
        );
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        // Generar PDF adjunto con los datos de la minuta
        $pdf = Pdf::loadView('emails.minuta_pdf', [
            'datos'  => $this->datos,
            'accion' => $this->accion,
        ])->setPaper('letter', 'portrait');

        $nombre = 'minuta_' . $this->datos['id'] . '.pdf';

        return [
            Attachment::fromData(fn () => $pdf->output(), $nombre)
                ->withMime('application/pdf'),
        ];
    }
}
