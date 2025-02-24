<?php
// commands/GenerateQRCommand.php

require_once __DIR__ . '/../helpers/Logger.php';
require_once __DIR__ . '/../vendor/qr-code/QrCode.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class GenerateQRCommand {
    private $bot;

    public function __construct($bot) {
        $this->bot = $bot;
    }

    public function execute($chatId, $userId, $text) {
        if (strpos($text, '/generateqr') === 0) {
            $url = trim(str_replace('/generateqr', '', $text));

            if (empty($url)) {
                $this->bot->sendMessage($chatId, "❌ Please provide a URL to generate the QR code.");
                return;
            }
            
            // Validate URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $this->bot->sendMessage($chatId, "❌ The provided URL is not valid. Please provide a valid URL.");
                return;
            }

            try {
                // Generate the QR Code
                $qrCode = new QrCode($url);
                $qrCode->setSize(300);          // Set QR code size
                $qrCode->setMargin(10);         // Set margin around the QR code
                $qrCode->setEncoding('UTF-8');  // Set encoding
                
                // Optional: تنظیم سطح تصحیح خطا و سایر تنظیمات می‌توانند اینجا اضافه شوند
                // $qrCode->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::HIGH());

                $writer = new PngWriter();
                $qrCodeImage = $writer->writeString($qrCode);

                if (empty($qrCodeImage)) {
                    throw new Exception("QR code image generation returned an empty result.");
                }

                // Send the QR code as an image with a caption showing the original URL
                $caption = "Here is your QR Code for: " . htmlspecialchars($url);
                $this->bot->sendPhoto($chatId, $qrCodeImage, $caption);
            } catch (Exception $e) {
                Logger::error("Error generating QR code: " . $e->getMessage());
                $this->bot->sendMessage($chatId, "❌ Error generating QR code. Please try again later.");
            }
        }
    }
}
?>
