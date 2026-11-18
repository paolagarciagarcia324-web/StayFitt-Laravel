<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecuperarContrasenaNotification extends Notification
{
    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $nombre = trim((string) $notifiable->name) ?: 'usuario StayFit';

        return (new MailMessage)
            ->subject('Recupera tu acceso a StayFit')
            ->view('emails.recuperar-contrasena', [
                'url' => $url,
                'nombre' => $nombre,
                'minutos' => 60,
            ]);
    }
}
