<?php
// core/Bot.php

class Bot {
    private $token;

    public function __construct($token) {
        $this->token = $token;
    }

    // Send message to a chat via Telegram Bot API
    public function sendMessage($chatId, $text) {
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        file_get_contents($url . '?' . http_build_query($data));
    }
}
