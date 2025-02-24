<?php
// main.php - Optimized Telegram Bot

require_once 'config/config.php';
require_once 'core/Bot.php';
require_once 'helpers/Loader.php';
require_once 'helpers/Logger.php';

$log = new Logger('bot.log');
$bot = new Bot(BOT_TOKEN);
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!isset($update['message'])) {
    exit;
}

$message = $update['message'];
$chatId = $message['chat']['id'];
$userId = $message['from']['id'];
$text = $message['text'] ?? '';

try {
    Loader::loadCommands($bot, $chatId, $userId, $text);
} catch (Exception $e) {
    $log->error("Error executing command: " . $e->getMessage());
    $bot->sendMessage($chatId, "❌ An error occurred. Please try again later.");
}

// ShortLink Handling
if (isset($_GET['short'])) {
    require_once 'core/ShortLinkService.php';
    $shortLinkService = new ShortLinkService();
    $shortCode = $_GET['short'];
    $originalUrl = $shortLinkService->getOriginalUrl($shortCode);
    
    if ($originalUrl) {
        $shortLinkService->incrementVisit($shortCode);
        header("Location: $originalUrl");
        exit;
    } else {
        echo "لینک کوتاه نامعتبر است.";
    }
}
