<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * A quote / invoice / reminder sent to a customer on behalf of a company (2026-11-24).
 * Sent from the platform address with the company's name; replies go to the company.
 */
class DocumentMail extends Mailable
{
    public function __construct(
        public string $sujet,
        public string $corps,
        public string $societeName,
        public ?string $replyToAddress,
        public ?string $pdf = null,
        public ?string $filename = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), $this->societeName.' via Stokito'),
            replyTo: $this->replyToAddress ? [new Address($this->replyToAddress, $this->societeName)] : [],
            subject: $this->sujet,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.document', text: 'mail.document-text');
    }

    public function attachments(): array
    {
        return $this->pdf ? [Attachment::fromData(fn () => $this->pdf, $this->filename)->withMime('application/pdf')] : [];
    }
}
