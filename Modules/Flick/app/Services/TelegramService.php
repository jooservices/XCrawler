<?php

namespace Modules\Flick\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected string $chatId;

    public function __construct()
    {
        $this->botToken = config('flick.telegram_bot_token', env('TELEGRAM_BOT_TOKEN'));
        $this->chatId = config('flick.telegram_chat_id', env('TELEGRAM_CHAT_ID'));
    }

    public function notify(string $message): void
    {
        if (empty($this->botToken) || empty($this->chatId)) {
            Log::warning("Telegram credentials not configured. Message skipped: $message");
            return;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if (!$response->successful()) {
                Log::error("Telegram notification failed", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Telegram connection error: " . $e->getMessage());
        }
    }
}
