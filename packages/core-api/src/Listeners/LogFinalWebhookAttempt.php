<?php

namespace Fleetbase\Listeners;

use Fleetbase\Webhook\Events\FinalWebhookCallFailedEvent;
use Fleetbase\Models\WebhookRequestLog;
use Illuminate\Support\Str;

class LogFinalWebhookAttempt
{
    /**
     * Handle the event.
     *
     * @param \Fleetbase\Webhook\Events\FinalWebhookCallFailedEvent $event
     * @return void
     */
    public function handle(FinalWebhookCallFailedEvent $event)
    {
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $event->response;
        /** @var \GuzzleHttp\TransferStats $stats */
        $stats = $event->transferStats;
        /** @var float $transferTime The time it took for the webhook to get a response */
        $transferTime = $stats->getTransferTime();
        /** @var string $connection The db connection the webhook was called on */
        $connection = (bool) data_get($event, 'meta.is_sandbox') ? 'sandbox' : 'mysql';
        /** @var int $statusCode The response status code */
        $statusCode = $response ? $response->getStatusCode() : 500;

        // log webhook event
        WebhookRequestLog::on($connection)->create([
            '_key' => data_get($event, 'meta.api_key'),
            'company_uuid' => data_get($event, 'meta.company_uuid'),
            'api_credential_uuid' => data_get($event, 'meta.api_credential_uuid'),
            'webhook_uuid' => data_get($event, 'meta.webhook_uuid'),
            'api_event_uuid' => data_get($event, 'meta.api_event_uuid'),
            'method' => $event->httpVerb,
            'status_code' => $response ? $response->getStatusCode() : 500,
            'reason_phrase' => $response ? $response->getReasonPhrase() : 'ERR',
            'duration' => $transferTime,
            'url' => $event->webhookUrl,
            'attempt' => $event->attempt,
            'response' => $response ? $response->getBody() : null,
            'status' => Str::startsWith((string) $statusCode, '2') ? 'successful' : 'failed',
            'headers' => $event->headers,
            'meta' => $event->meta,
            'sent_at' => data_get($event, 'meta.sent_at'),
        ]);
    }
}
