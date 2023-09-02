<?php

namespace Fleetbase\Notifications;

use Fleetbase\Models\VerificationCode;
use Fleetbase\Support\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class UserForgotPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public ?VerificationCode $verificationCode;
    public string $url;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(?VerificationCode $verificationCode)
    {
        $this->verificationCode = $verificationCode;
        $this->url = Utils::consoleUrl('auth/reset-password/' . $verificationCode->uuid, ['code' => $verificationCode->code]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your password reset link for Fleetbase')
            ->greeting('Hello, ' . $notifiable->name)
            ->line('Looks like you (or someone phishy) has requested to reset your password. If you did not request a password reset link, ignore this email. If you have indeed forgot your password click the button below to reset your password using the code provided below.')
            ->line(new HtmlString('<br><p style="font-family: monospace;">Your password reset code: <strong>' . $this->verificationCode->code . '</strong></p>'))
            ->action('Reset Password', $this->url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
