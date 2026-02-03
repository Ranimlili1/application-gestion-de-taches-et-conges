<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;
    public string $email;

    public function __construct(string $token, string $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        $resetUrl = "http://localhost:4200/reset-password?token={$this->token}&email={$this->email}";

        return $this->subject('Réinitialisation de votre mot de passe')
                    ->view('emails.reset_password')
                    ->with([
                        'resetUrl' => $resetUrl,
                        'email' => $this->email
                    ]);
    }
}
