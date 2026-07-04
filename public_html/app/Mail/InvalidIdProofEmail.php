<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvalidIdProofEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $userData;

    public function __construct($userData)
    {
        $this->userData = $userData;
    }

    public function build()
    {
        return $this
        ->from('support@cognispheremc.com', 'LMS Support')
        ->subject("Info Required")
        ->view('emails.invalid_id_proof')
        ->with([
            'profile' => $this->userData,
        ]);
    }
}
