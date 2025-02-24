<?php
// core/setWebhook.php
require_once '../config/config.php';

$webhookUrl = '[{your-url}/main]';

$response = file_get_contents("https://api.telegram.org/bot" . BOT_TOKEN . "/setWebhook?url=" . $webhookUrl);

if ($response) {
    echo "Webhook has been set successfully!";
} else {
    echo "Failed to set Webhook!";
}
