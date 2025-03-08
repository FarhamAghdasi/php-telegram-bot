<?php
// commands/AboutCommand.php

require_once __DIR__ . '/../helpers/Logger.php';

class AboutCommand {
    private $bot;

    public function __construct($bot) {
        $this->bot = $bot;
    }

    public function execute($chatId, $userId, $text) {
        if (strpos($text, '/about') === 0) {
            $message = "👤 <b>Bot Creator:</b> " . CREATOR_NAME . "\n\n";
            
            $message .= "🌐 <b>Contact With Creator:</b>\n";
            $message .= "📸 <a href='" . CREATOR_INSTAGRAM . "'>Instagram</a>\n";
            $message .= "🖥️ <a href='" . CREATOR_WEBSITE . "'>Website</a>\n\n";
            
            $message .= "📜 " . COPYRIGHT_TEXT;

            $this->bot->sendMessage($chatId, $message, 'HTML');
        }
    }
}