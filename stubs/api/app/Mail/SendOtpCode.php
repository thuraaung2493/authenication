<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use function config;

final class SendOtpCode extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Otp $otp,
        public User|null $user = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                address: config('mail.from.address'),
                name: config('mail.from.name'),
            ),
            subject: 'Your OTP code for account registration',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.send-otp',
            with: [
                'user' => $this->user,
                'otp' => $this->otp,
            ],
        );
    }
}
