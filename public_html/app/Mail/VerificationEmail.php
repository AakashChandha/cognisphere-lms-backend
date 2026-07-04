<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $profile;

    public function __construct($profile)
    {
        $this->profile = $profile;
    }

    public function build()
    {
        return $this
            ->from('support@cognispheremc.com', 'LMS Credentials')
            ->subject("Your Login Credentials")
            ->view('emails.verification')
            ->with([
                'profile' => $this->profile,
            ]);
    }
}