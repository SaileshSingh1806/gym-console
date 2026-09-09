<?php

namespace App\Services\Notification;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function send(
        User|Member $recipient,
        string $title,
        string $message,
        array $channels = ['email']
    ): array {
        $results = [];

        foreach ($channels as $channel) {
            $results[$channel] = match ($channel) {
                'email' => $this->sendEmail($recipient, $title, $message),
                'sms' => $this->sendSms($recipient, $message),
                'whatsapp' => $this->sendWhatsApp($recipient, $message),
                'push' => $this->sendPush($recipient, $title, $message),
                default => false,
            };
        }

        return $results;
    }

    protected function sendEmail(User|Member $recipient, string $title, string $message): bool
    {
        if (empty($recipient->email)) {
            return false;
        }

        Log::info("Dispatching Email to [{$recipient->email}]: {$title} - {$message}");

        return true;
    }

    protected function sendSms(User|Member $recipient, string $message): bool
    {
        if (empty($recipient->phone)) {
            return false;
        }

        Log::info("Dispatching SMS to [{$recipient->phone}]: {$message}");

        return true;
    }

    protected function sendWhatsApp(User|Member $recipient, string $message): bool
    {
        if (empty($recipient->phone)) {
            return false;
        }

        Log::info("Dispatching WhatsApp message to [{$recipient->phone}]: {$message}");

        return true;
    }

    protected function sendPush(User|Member $recipient, string $title, string $message): bool
    {
        Log::info("Dispatching Push Notification to user/member [{$recipient->id}]: {$title}");

        return true;
    }
}
