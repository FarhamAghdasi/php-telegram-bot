<?php
// commands/QRCodeCommand.php

require_once __DIR__ . '/../helpers/Logger.php';
require_once __DIR__ . '/../libs/qr/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QRCodeCommand {
    private $bot;
    private $projectRoot;

    public function __construct($bot) {
        $this->bot = $bot;
        $this->projectRoot = realpath(__DIR__ . '/..');
    }

    public function execute($chatId, $userId, $message) {
        if (!isset($message['text']) || strpos($message['text'], '/qrcode') !== 0) {
            return;
        }

        $this->handleTextCommand($chatId, $message['text']);
    }

    private function handleTextCommand($chatId, $text) {
        $parts = explode(' ', $text, 3);
        $sub = strtolower($parts[1] ?? '');

        switch ($sub) {
            case 'text':
            case 'url':
                if (empty($parts[2])) {
                    $this->bot->sendMessage($chatId, "❌ Please provide content.\nUse /qrcode help");
                    return;
                }
                $this->generateQr($chatId, trim($parts[2]));
                break;

            case 'help':
            default:
                $this->showHelp($chatId);
        }
    }

    private function showHelp($chatId) {
        $help = <<<TXT
🧾 *QR Code Bot Commands*:
/qrcode text [text] – Generate QR from any text
/qrcode url [url] – Generate QR from a link

مثال:
/qrcode text سلام دنیا
/qrcode url https://example.com
TXT;
        $this->bot->sendMessage($chatId, $help, 'Markdown');
    }

    private function generateQr($chatId, $content) {
        try {
            $qr = new QrCode($content);
            $writer = new PngWriter();
            $result = $writer->write($qr);

            $filePath = $this->projectRoot . '/data/qr_' . md5($content) . '.png';
            file_put_contents($filePath, $result->getString());

            $this->bot->sendPhoto($chatId, new CURLFile($filePath), "✅ QR Code generated.");
        } catch (Exception $e) {
            Logger::error("QR generation failed: " . $e->getMessage());
            $this->bot->sendMessage($chatId, "❌ Failed to generate QR code.");
        }
    }
}
