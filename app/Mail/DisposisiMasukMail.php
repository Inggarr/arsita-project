<?php

namespace App\Mail;

use App\Models\Disposisi;
use App\Models\DisposisiTujuan;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DisposisiMasukMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public SuratMasuk $suratMasuk,
        public Disposisi $disposisi,
        public User $penerima,
        public ?DisposisiTujuan $disposisiTujuan = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[ARSITA UINSSC] Disposisi Surat Masuk: '.$this->suratMasuk->perihal,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.disposisi_masuk',
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
