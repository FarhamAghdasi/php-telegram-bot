<?php
// commands/GetIframeCommand.php

require_once __DIR__ . '/../helpers/Logger.php';

class GetIframeCommand {
    private $bot;

    public function __construct($bot) {
        $this->bot = $bot;
    }

    public function execute($chatId, $userId, $text) {
        Logger::info("Checking GetIframeCommand for text: $text");
        // Ensure the user is authorized
        if (!in_array((string)$userId, ALLOWED_USER_IDS)) {
            Logger::error("Access denied for user $userId.");
            $this->bot->sendMessage($chatId, "⚠️ Access denied: You are not authorized to use this bot.");
            return;
        }        

        // Check if the command is /getiframe
        if (strpos($text, '/getiframe') === 0) {
            Logger::info("GetIframe command matched for user $userId");
            $parts = explode(' ', $text, 2);

            // Ensure a URL is provided
            if (count($parts) < 2 || empty($parts[1])) {
                $this->bot->sendMessage($chatId, "❌ Usage: /getiframe [URL]\nPlease provide a valid URL.");
                return;
            }

            $url = trim($parts[1]);

            // Call the iframe extractor script
            $result = $this->fetchIframeUrl($url);

            if (isset($result['final_url'])) {
                $this->bot->sendMessage($chatId, "✅ Iframe URL: " . $result['final_url']);
            } else {
                $error = $result['error'] ?? 'Unknown error occurred.';
                $this->bot->sendMessage($chatId, "❌ Error: $error");
            }
        } else {
            Logger::info("GetIframe command not matched for text: $text");
        }
    }

    // Function to call the iframe extractor script
    private function fetchIframeUrl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, IFRAME_EXTRACTOR_URL . '?url=' . urlencode($url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
