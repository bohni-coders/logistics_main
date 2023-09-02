<?php

namespace Fleetbase\Storefront\Notifications;

use Exception;
use Fleetbase\FleetOps\Models\Order;
use Fleetbase\Models\Storefront\NotificationChannel;
use Fleetbase\Storefront\Support\Storefront;
use Fleetbase\FleetOps\Support\Utils;
// use Fleetbase\FleetOps\Support\Utils;
use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;
use Pushok\Client as PushOkClient;
use Pushok\AuthProvider\Token as PuskOkToken;

class StorefrontOrderDriverAssigned extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order->setRelations([]);
        $this->driver = $order->driverAssigned;
        $this->storefront = Storefront::findAbout($this->order->getMeta('storefront_id'));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // $channels = ['mail'];
        $channels = [];

        if (!$this->storefront) {
            return $channels;
        }

        $hasApnNotificationChannels = NotificationChannel::where(['owner_uuid' => $this->storefront->uuid, 'scheme' => 'apn'])->count();
        $hasFcmNotificationChannels = NotificationChannel::where(['owner_uuid' => $this->storefront->uuid, 'scheme' => 'android'])->count();

        if ($hasApnNotificationChannels) {
            $channels[] = ApnChannel::class;
        }
        if ($hasFcmNotificationChannels) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Your order from ' . $this->storefront->name . ' is being prepared')
            ->line('Your order is getting started.');

        // $message->action('View Details', Utils::consoleUrl('', ['shift' => 'fleet-ops/orders/view/' . $this->order->public_id]));

        return $message;
    }

    /**
     * Get the firebase cloud message representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toFcm($notifiable)
    {
        $notification = \NotificationChannels\Fcm\Resources\Notification::create()
            ->setTitle('Your driver is ' . $this->driver->name)
            ->setBody('A driver has been assigned to your order, they are en-route for pickup');

        $message = FcmMessage::create()
            ->setData(['order' => $this->order->uuid, 'id' => $this->order->public_id, 'type' => 'order_driver_assigned'])
            ->setNotification($notification)
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('analytics'))
                    ->setNotification(AndroidNotification::create()->setColor('#4391EA'))
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('analytics_ios'))
            );

        return $message;
    }

    public function fcmProject($notifiable, $message)
    {
        $about = Storefront::findAbout($this->order->getMeta('storefront_id'));

        if ($this->order->hasMeta('storefront_notification_channel')) {
            $notificationChannel = NotificationChannel::where([
                'owner_uuid' => $about->uuid,
                'app_key' => $this->order->getMeta('storefront_notification_channel'),
                'scheme' => 'fcm'
            ])->first();
        } else {
            $notificationChannel = NotificationChannel::where([
                'owner_uuid' => $about->uuid,
                'scheme' => 'fcm'
            ])->first();
        }

        if (!$notificationChannel) {
            return 'app';
        }

        $this->configureFcm($notificationChannel);

        return $notificationChannel->app_key;
    }

    public function configureFcm($notificationChannel)
    {
        $config = (array) $notificationChannel->config;
        $fcmConfig = config('firebase.projects.app');

        // set credentials
        Utils::set($fcmConfig, 'credentials.file', $config['firebase_credentials_json']);

        // set db url
        Utils::set($fcmConfig, 'database.url', $config['firebase_database_url']);

        config('firebase.projects.' . $notificationChannel->app_key, $fcmConfig);

        return $fcmConfig;
    }

    /**
     * Get the apns message representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toApn($notifiable)
    {
        $about = Storefront::findAbout($this->order->getMeta('storefront_id'));

        if ($this->order->hasMeta('storefront_notification_channel')) {
            $notificationChannel = NotificationChannel::where([
                'owner_uuid' => $about->uuid,
                'app_key' => $this->order->getMeta('storefront_notification_channel'),
                'scheme' => 'apn'
            ])->first();
        } else {
            $notificationChannel = NotificationChannel::where([
                'owner_uuid' => $about->uuid,
                'scheme' => 'apn'
            ])->first();
        }

        $config = (array) $notificationChannel->config;

        try {
            $channelClient = new PushOkClient(PuskOkToken::create($config));
        } catch (Exception $e) {
            // stop silently
            return;
        }

        $message = ApnMessage::create()
            ->badge(1)
            ->title('Your driver is ' . $this->driver->name)
            ->body('A driver has been assigned to your order, they are en-route for pickup')
            ->custom('type', 'order_driver_assigned')
            ->custom('order', $this->order->uuid)
            ->custom('id', $this->order->public_id)
            ->via($channelClient);

        return $message;
    }
}
