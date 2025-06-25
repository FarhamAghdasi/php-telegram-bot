<?php
// MyInfoCommand.php - Optimized to display complete user info with profile photo

require_once __DIR__ . '/../helpers/Logger.php';

class MyInfoCommand
{
    private $bot;

    public function __construct($bot)
    {
        $this->bot = $bot;
    }

    public function execute($chatId, $userId, $text)
    {
        Logger::info("Checking MyInfoCommand for text: $text");
        if (strpos($text, '/myinfo') !== 0) {
            Logger::info("MyInfo command not matched for text: $text");
            return;
        }

        // Retrieve update data (fallback in case it wasn't passed from main)
        $inputData = file_get_contents("php://input");
        $update = json_decode($inputData, true);
        if (!isset($update['message'])) {
            Logger::error("No message found in update.");
            $this->bot->sendMessage($chatId, "❌ Unable to retrieve user information.");
            return;
        }
        $user = $update['message']['from'] ?? null;
        if (!$user) {
            Logger::error("No user data found in update.");
            $this->bot->sendMessage($chatId, "❌ Unable to retrieve user information.");
            return;
        }

        // Build detailed user info
        $userInfo = "👤 <b>User Info:</b>\n";
        $userInfo .= "🆔 <b>User ID:</b> <code>" . htmlspecialchars($user['id']) . "</code>\n";
        $userInfo .= "👤 <b>First Name:</b> " . htmlspecialchars($user['first_name'] ?? 'N/A') . "\n";
        $userInfo .= "👤 <b>Last Name:</b> " . htmlspecialchars($user['last_name'] ?? 'N/A') . "\n";
        $userInfo .= "👤 <b>Username:</b> " . htmlspecialchars($user['username'] ?? 'N/A') . "\n";
        $userInfo .= "🌐 <b>Language:</b> " . htmlspecialchars($user['language_code'] ?? 'N/A') . "\n";
        if (isset($user['bio'])) {  // در صورت وجود بیوگرافی کاربر
            $userInfo .= "📝 <b>Bio:</b> " . htmlspecialchars($user['bio']) . "\n";
        }

        // Chat ID Copy Link
        $copyChatIdLink = "<a href=\"https://t.me/share/url?url=" . urlencode($user['id']) . "\">📋 Copy User ID</a>";

        Logger::info("User $userId requested their info.");

        // Fetch user profile photo
        $profilePhotoUrl = null;
        $photos = $this->bot->getUserProfilePhotos($userId);
        if (isset($photos['result']['photos'][0][0]['file_id'])) {
            $fileId = $photos['result']['photos'][0][0]['file_id'];
            $fileData = $this->bot->getFile($fileId);
            if (isset($fileData['result']['file_path'])) {
                $profilePhotoUrl = "https://api.telegram.org/file/bot" . BOT_TOKEN . "/" . $fileData['result']['file_path'];
            }
        }

        // Send message with profile photo if available, otherwise just text
        if ($profilePhotoUrl) {
            $this->bot->sendPhoto($chatId, $profilePhotoUrl, $userInfo . "\n" . $copyChatIdLink, 'HTML');
        } else {
            $this->bot->sendMessage($chatId, $userInfo . "\n" . $copyChatIdLink, 'HTML');
        }
    }
}
