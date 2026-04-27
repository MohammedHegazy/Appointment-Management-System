<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $fullName,
        public string $otpCode
    ) {
    }

    public function build(): self
    {
        return $this->subject('Verify your email address')
            ->view('emails.otp');
    }
}
