<?php

namespace App\Workers\Pusher;

use App\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class Pusher
{
    private WebPush $webPush;

    public function __construct()
    {
        $keys = json_decode(file_get_contents(CACHE_PATH . 'vapid_keys.json'));

        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => 'https://movim.eu',
                'publicKey' => $keys->publicKey,
                'privateKey' => $keys->privateKey
            ]
        ]);
    }

    public function send(
        string $userId,
        string $title,
        ?string $body = null,
        ?string $picture = null,
        ?string $action = null,
        ?string $group = null,
        ?string $execute = null
    ): void {
        foreach (
            PushSubscription::where('user_id', $userId)
                ->where('enabled', true)
                ->whereNotNull('activity_at')->get() as $pushSubscription
        ) {
            $this->webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $pushSubscription->endpoint,
                    'contentEncoding' => 'aesgcm',
                    'keys' => [
                        'auth' => $pushSubscription->auth,
                        'p256dh' => $pushSubscription->p256dh
                    ]
                ]),
                json_encode([
                    'title' => $title,
                    'body' => $body,
                    'picture' => $picture,
                    'action' => $action,
                    'group' => $group,
                    'execute' => $execute,
                    'button' => __('button.open')
                ])
            );
        }

        foreach ($this->webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                PushSubscription::where('user_id', $userId)
                    ->where('endpoint', $report->getEndpoint())
                    ->delete();
            }
        }
    }
}
