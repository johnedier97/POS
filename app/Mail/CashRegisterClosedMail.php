<?php

namespace App\Mail;

use App\Models\CashRegisterSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CashRegisterClosedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $session;

    public $totalSales;

    public $reportedCash;

    public $difference;

    /**
     * Create a new message instance.
     */
    public function __construct(CashRegisterSession $session, $totalSales, $reportedCash)
    {
        $this->session = $session;
        $this->totalSales = $totalSales;
        $this->reportedCash = $reportedCash;
        $this->difference = $reportedCash - ($session->initial_balance + $totalSales);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notificación de Cierre de Caja - '.($this->session->cashRegister->branch->name ?? 'Sucursal'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.cash_register_closed',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
