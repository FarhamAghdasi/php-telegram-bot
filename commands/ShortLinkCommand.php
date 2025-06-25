<?php
// commands/shortlink.php

require_once __DIR__ . '/../core/ShortLinkService.php';
require_once __DIR__ . '/../helpers/Logger.php';

class ShortLinkCommand {
    private $bot;
    private $shortLinkService;

    public function __construct($bot) {
        $this->bot = $bot;
        $this->shortLinkService = new ShortLinkService();
    }

    public function execute($chatId, $userId, $text) {
        if (!in_array((string)$userId, ALLOWED_USER_IDS)) {
            Logger::error("Access denied for user $userId.");
            $this->bot->sendMessage($chatId, "⚠️ Access denied: You are not authorized to use this bot.");
            return;
        }
        
        // بررسی دستور
        if (strpos($text, '/shortlink') === 0) {
            $parts = explode(' ', $text, 2);

            if (count($parts) < 2) {
                $this->bot->sendMessage($chatId, "لطفاً یک لینک معتبر وارد کنید. مثال: `/shortlink https://example.com`");
                return;
            }

            $originalUrl = trim($parts[1]);

            // ولیدیت لینک
            if (!filter_var($originalUrl, FILTER_VALIDATE_URL)) {
                $this->bot->sendMessage($chatId, "لینک وارد شده معتبر نیست. لطفاً دوباره تلاش کنید.");
                return;
            }

            // کوتاه کردن لینک
            $shortCode = $this->shortLinkService->shortenLink($originalUrl);
            $shortLink = "https://api.farhamaghdasi.ir/telegram-bot/data/short?short=" . $shortCode;

            $this->bot->sendMessage($chatId, "لینک کوتاه شما:\n" . $shortLink);
        }
    }
}
