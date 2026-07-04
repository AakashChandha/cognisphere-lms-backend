<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
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
            ->subject("Welcome to Cognisphere")
            ->view('emails.welcome')
            ->with([
                'profile' => $this->profile,
            ]);
    }
}