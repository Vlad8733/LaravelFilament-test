<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function dispatch(string $event, array $payload): void
    {
        $webhooks = Webhook::forEvent($event);

        foreach ($webhooks as $webhook) {
            /** @var Webhook $webhook */
            $this->send($webhook, $event, $payload);
        }
    }

    public function send(Webhook $webhook, string $event, array $payload, int $attempt = 1): WebhookLog
    {
        $fullPayload = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => $payload,
        ];

        $jsonPayload = json_encode($fullPayload);

        $log = WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'payload' => $fullPayload,
            'attempt' => $attempt,
            'status' => WebhookLog::STATUS_PENDING,
        ]);

        try {
            $headers = [
                'Content-Type' => 'application/json',
                'X-Webhook-Event' => $event,
                'X-Webhook-Timestamp' => (string) now()->timestamp,
            ];

            if ($signature = $webhook->generateSignature($jsonPayload ?: '')) {
                $headers['X-Webhook-Signature'] = $signature;
            }

            $response = Http::timeout($webhook->timeout_seconds)
                ->withHeaders($headers)
                ->withBody($jsonPayload ?: '', 'application/json')
                ->post($webhook->url);

            if ($response->successful()) {
                $log->markAsSuccess($response->status(), $response->body());
                $webhook->update(['last_triggered_at' => now()]);
            } else {
                $log->markAsFailed($response->status(), $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Webhook delivery failed', [
                'webhook_id' => $webhook->id,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            $log->markAsFailed(null, $e->getMessage());
        }

        return $log;
    }

    public function retry(WebhookLog $log): ?WebhookLog
    {
        if (! $log->canRetry()) {
            return null;
        }

        /** @var Webhook $wh */
        $wh = $log->webhook;

        return $this->send(
            $wh,
            $log->event,
            $log->payload['data'] ?? [],
            $log->attempt + 1
        );
    }
}
