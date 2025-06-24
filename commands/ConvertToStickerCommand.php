<?php
// commands/ConvertToStickerCommand.php

require_once __DIR__ . '/../helpers/Logger.php';

class ConvertToStickerCommand {
    private $bot;
    private $stickersDir;
    private $fontsDir;
    private $availableFonts = [
        'Vazir' => 'Vazir.ttf',
        'Sahel' => 'sahel.ttf',
        'IranNastaliq' => 'IranNastaliq.ttf',
    ];

    public function __construct($bot) {
        $this->bot = $bot;
        $this->stickersDir = __DIR__ . '/../stickers/';
        $this->fontsDir = __DIR__ . '/../fonts/';

        // ایجاد پوشه استیکرها اگر وجود نداشته باشد
        if (!is_dir($this->stickersDir)) {
            mkdir($this->stickersDir, 0777, true);
        }
    }

    public function execute($chatId, $userId, $text) {
        // بررسی دسترسی کاربر
        if (!in_array((string)$userId, ALLOWED_USER_IDS)) {
            Logger::error("Access denied for user $userId on /convert_to_sticker command.");
            $this->bot->sendMessage($chatId, "⚠️ Access denied: You are not authorized to use this command.");
            return;
        }

        // بررسی دستور /convert_to_sticker
        if (strpos($text, '/convert_to_sticker') !== 0) {
            return;
        }

        $parts = explode(' ', $text, 3);
        $action = $parts[1] ?? '';

        // دریافت اطلاعات آپدیت
        $inputData = file_get_contents("php://input");
        $update = json_decode($inputData, true);
        $message = $update['message'] ?? [];

        // اگر دستور خالی باشد، راهنما نمایش داده شود
        if (empty($action)) {
            $this->showHelp($chatId);
            return;
        }

        switch (strtolower($action)) {
            case 'create':
                if (count($parts) < 3) {
                    $this->bot->sendMessage($chatId, "❌ Usage: /convert_to_sticker create [font] [text]");
                    return;
                }
                $font = $parts[1];
                $stickerText = $parts[2] ?? '';
                $this->createSticker($chatId, $userId, $message, $font, $stickerText);
                break;
            default:
                $this->showHelp($chatId);
        }
    }

    private function showHelp($chatId) {
        $message = "ℹ️ Convert to Sticker Command Usage:\n\n";
        $message .= "🖼 /convert_to_sticker create [font] [text]\n";
        $message .= "🔍 Example: /convert_to_sticker create Vazir Hello\n";
        $message .= "🔹 font: Available fonts (" . implode(', ', array_keys($this->availableFonts)) . ")\n";
        $message .= "🔹 text: Text to add to the sticker\n\n";
        $message .= "📌 Steps:\n1. Send an image to the bot.\n2. Use the command with font and text.\n";
        $message .= "⚠️ Note: Image must be sent before the command, and only PNG/JPEG formats are supported.";
        $this->bot->sendMessage($chatId, $message);
    }

    private function createSticker($chatId, $userId, $message, $font, $stickerText) {
        // بررسی وجود فونت
        if (!isset($this->availableFonts[$font])) {
            $this->bot->sendMessage($chatId, "❌ Invalid font. Available fonts: " . implode(', ', array_keys($this->availableFonts)));
            return;
        }

        // بررسی وجود عکس در پیام
        if (!isset($message['photo'])) {
            $this->bot->sendMessage($chatId, "❌ Please send an image first, then use the command.");
            return;
        }

        // دریافت فایل عکس
        $photo = end($message['photo']); // انتخاب بزرگ‌ترین اندازه
        $fileId = $photo['file_id'];
        $fileData = $this->bot->getFile($fileId);
        if (!isset($fileData['result']['file_path'])) {
            Logger::error("Failed to get file path for file ID: $fileId");
            $this->bot->sendMessage($chatId, "❌ Failed to retrieve image.");
            return;
        }

        $fileUrl = "https://api.telegram.org/file/bot" . BOT_TOKEN . "/" . $fileData['result']['file_path'];
        $imageContent = file_get_contents($fileUrl);
        if ($imageContent === false) {
            Logger::error("Failed to download image from: $fileUrl");
            $this->bot->sendMessage($chatId, "❌ Failed to download image.");
            return;
        }

        // ذخیره موقت تصویر
        $tempImagePath = $this->stickersDir . uniqid('temp_') . '.png';
        file_put_contents($tempImagePath, $imageContent);

        // پردازش تصویر و افزودن متن
        $stickerPath = $this->processImage($tempImagePath, $font, $stickerText);
        if (!$stickerPath) {
            Logger::error("Failed to process image for user $userId");
            $this->bot->sendMessage($chatId, "❌ Failed to create sticker.");
            unlink($tempImagePath);
            return;
        }

        // ارسال استیکر
        $this->sendSticker($chatId, $stickerPath);

        // پاک‌سازی فایل‌های موقت
        unlink($tempImagePath);
        unlink($stickerPath);

        Logger::info("Sticker created and sent to user $userId with font $font and text: $stickerText");
    }

    private function processImage($imagePath, $font, $stickerText) {
        // بارگذاری تصویر
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return false;
        }

        switch ($imageInfo['mime']) {
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            default:
                return false;
        }

        // تغییر اندازه به 512x512
        $stickerSize = 512;
        $resizedImage = imagecreatetruecolor($stickerSize, $stickerSize);
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
        $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
        imagefill($resizedImage, 0, 0, $transparent);

        $srcWidth = imagesx($image);
        $srcHeight = imagesy($image);
        $ratio = min($stickerSize / $srcWidth, $stickerSize / $srcHeight);
        $destWidth = (int)($srcWidth * $ratio);
        $destHeight = (int)($srcHeight * $ratio);
        $destX = ($stickerSize - $destWidth) / 2;
        $destY = ($stickerSize - $destHeight) / 2;

        imagecopyresampled($resizedImage, $image, $destX, $destY, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight);
        imagedestroy($image);

        // افزودن متن
        $fontPath = $this->fontsDir . $this->availableFonts[$font];
        $fontSize = 30;
        $textColor = imagecolorallocate($resizedImage, 255, 255, 255); // سفید
        $textBox = imagettfbbox($fontSize, 0, $fontPath, $stickerText);
        $textWidth = $textBox[2] - $textBox[0];
        $textHeight = $textBox[1] - $textBox[7];
        $textX = ($stickerSize - $textWidth) / 2;
        $textY = $stickerSize - 20; // قرار دادن متن در پایین تصویر

        // افزودن سایه برای خوانایی
        $shadowColor = imagecolorallocate($resizedImage, 0, 0, 0); // مشکی
        imagettftext($resizedImage, $fontSize, 0, $textX + 2, $textY + 2, $shadowColor, $fontPath, $stickerText);
        imagettftext($resizedImage, $fontSize, 0, $textX, $textY, $textColor, $fontPath, $stickerText);

        // ذخیره استیکر
        $stickerPath = $this->stickersDir . uniqid('sticker_') . '.png';
        imagepng($resizedImage, $stickerPath);
        imagedestroy($resizedImage);

        return $stickerPath;
    }

    private function sendSticker($chatId, $stickerPath) {
        $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendSticker";
        $postFields = [
            'chat_id' => $chatId,
            'sticker' => new CURLFile($stickerPath, 'image/png', basename($stickerPath))
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response || !json_decode($response, true)['ok']) {
            Logger::error("Failed to send sticker to chat $chatId: $response");
            $this->bot->sendMessage($chatId, "❌ Failed to send sticker.");
        }
    }
}