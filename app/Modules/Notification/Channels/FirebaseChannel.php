<?php

namespace App\Modules\Notification\Channels;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class FirebaseChannel
{
    public function __construct(
        private Messaging $messaging
    ) {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        $message = $notification->toFirebase($notifiable);

        $tokens = $notifiable->routeNotificationForFirebase();

        foreach ($tokens as $token) {
            $cloudMessage = CloudMessage::withTarget('token', $token)
                ->withNotification(
                    FirebaseNotification::create(
                        $message['title'],
                        $message['body']
                    )
                )
                ->withData($message['data'] ?? []);

            $this->messaging->send($cloudMessage);
        }
    }
}