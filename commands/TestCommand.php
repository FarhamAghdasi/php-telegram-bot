<?php
class TestCommand {
    private $bot;

    public function __construct($bot) {
        $this->bot = $bot;
    }

    public function execute($chatId, $userId, $text) {
        if (strpos($text, '/test') === 0) {
            $this->bot->sendMessage($chatId, "✅ تست موفقیت‌آمیز بود!");
        }
    }
}