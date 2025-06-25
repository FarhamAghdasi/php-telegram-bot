<?php
require_once __DIR__ . '/../config/config.php';

$webhookUrl = getenv('WEBHOOK_URL');
$botToken = BOT_TOKEN;

$response = file_get_contents("https://api.telegram.org/bot{$botToken}/setWebhook?url={$webhookUrl}");

echo "Set Webhook Result: " . $response;
