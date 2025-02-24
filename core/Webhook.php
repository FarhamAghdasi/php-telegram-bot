<?php
// core/Webhook.php

class Webhook {
    private $apiUrl = 'https://api.telegram.org/bot' . BOT_TOKEN . '/setWebhook';

    // Set the webhook for the bot
    public function setWebhook($url) {
        $response = file_get_contents($this->apiUrl . "?url=" . urlencode($url));
        $result = json_decode($response, true);
        
        if ($result['ok']) {
            echo "Webhook has been set successfully!";
        } else {
            echo "Error setting webhook!";
        }
    }

    // Remove the webhook for the bot
    public function removeWebhook() {
        $response = file_get_contents($this->apiUrl . "?url=");
        $result = json_decode($response, true);

        if ($result['ok']) {
            echo "Webhook has been removed!";
        } else {
            echo "Error removing webhook!";
        }
    }
}
