<?php

namespace Fleetbase\FleetOps\Notifications;

use Fleetbase\FleetOps\Events\OrderDispatchFailed as OrderDispatchFailedEvent;
use Fleetbase\FleetOps\Models\Order;
use Fleetbase\FleetOps\Support\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDispatchFailed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The order instance this notification is for.
     *
     * @var \Fleetbase\Models\Order
     */
    public $order;

    /**
     * Reason order dispatch failed.
     *
     * @var string
     */
    public $reason;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, OrderDispatchFailedEvent $event)
    {
        $this->order = $order->setRelations([]);
        $this->reason = $event->getReason();
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
            ->subject('Order ' . $this->order->public_id . ' has dispatch has failed!')
            ->line($this->reason)
            ->action('View Details', Utils::consoleUrl('', ['shift' => 'fleet-ops/orders/view/' . $this->order->public_id]));
    }
}
