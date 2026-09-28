<?php

namespace App\Mail;

use App\Models\Payroll;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayrollSlipMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payroll $payroll,
        private readonly string $pdfContent,
        private readonly string $pdfFilename,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Slip Gaji '.$this->payroll->period->period_date->locale('id')->translatedFormat('F Y'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.payroll-slip');
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn (): string => $this->pdfContent, $this->pdfFilename)
                ->withMime('application/pdf'),
        ];
    }
}
