<?php
// commands/SearchImageCommand.php

require_once __DIR__ . '/../helpers/Logger.php';

// use DOMDocument;
// use DOMXPath;

class SearchImageCommand {
    private $bot;
    private $baseUrl = 'https://www.google.com/search?tbm=isch';

    public function __construct($bot) {
        $this->bot = $bot;
    }

    public function execute($chatId, $userId, $text) {
        // بررسی دسترسی کاربر
        if (!in_array((string)$userId, ALLOWED_USER_IDS)) {
            Logger::error("Access denied for user $userId on /search_image command.");
            $this->bot->sendMessage($chatId, "⚠️ Access denied: You are not authorized to use this command.");
            return;
        }

        // بررسی دستور /search_image
        if (strpos($text, '/search_image') !== 0) {
            return;
        }

        $parts = explode(' ', $text, 4);
        $action = $parts[1] ?? '';

        // اگر دستور خالی باشد، راهنما نمایش داده شود
        if (empty($action)) {
            $this->showHelp($chatId);
            return;
        }

        switch (strtolower($action)) {
            case 'search':
                if (count($parts) < 3) {
                    $this->bot->sendMessage($chatId, "❌ Usage: /search_image search [query] [count] [safe=on|off]");
                    return;
                }
                $query = $parts[2] ?? '';
                $count = isset($parts[3]) && is_numeric($parts[3]) ? min((int)$parts[3], 10) : 5; // حداکثر 10 تصویر
                $safeSearch = isset($parts[4]) && strtolower($parts[4]) === 'off' ? 'off' : 'active';
                $this->searchImages($chatId, $query, $count, $safeSearch);
                break;
            default:
                $this->showHelp($chatId);
        }
    }

    private function showHelp($chatId) {
        $message = "ℹ️ Image Search Command Usage:\n\n";
        $message .= "📸 /search_image search [query] [count] [safe=on|off]\n";
        $message .= "🔍 Example: /search_image search cats 5 off\n";
        $message .= "🔹 query: Search term (e.g., cats, cars)\n";
        $message .= "🔹 count: Number of images (1-10, default 5)\n";
        $message .= "🔹 safe: SafeSearch mode (on=filtered, off=unfiltered, default on)\n\n";
        $message .= "⚠️ Note: This command uses web scraping and may be subject to Google's restrictions.";
        $this->bot->sendMessage($chatId, $message);
    }

    private function searchImages($chatId, $query, $count, $safeSearch) {
        if (empty($query)) {
            $this->bot->sendMessage($chatId, "❌ Please provide a search query.");
            return;
        }

        // ساخت URL جستجو
        $url = $this->baseUrl . '&' . http_build_query([
            'q' => $query,
            'safe' => $safeSearch,
        ]);

        // دریافت محتوای صفحه
        $html = $this->fetchPageContent($url);
        if (!$html) {
            Logger::error("Failed to fetch Google search results for query: $query");
            $this->bot->sendMessage($chatId, "❌ Failed to fetch images. Please try again later.");
            return;
        }

        // استخراج لینک‌های تصاویر
        $imageUrls = $this->parseImageUrls($html, $count);
        if (empty($imageUrls)) {
            Logger::info("No images found for query: $query");
            $this->bot->sendMessage($chatId, "❌ No images found for '$query'.");
            return;
        }

        // ارسال تصاویر به کاربر
        foreach ($imageUrls as $index => $imageUrl) {
            $caption = "Image " . ($index + 1) . " for '$query'";
            $this->bot->sendPhoto($chatId, $imageUrl, $caption);
            Logger::info("Sent image " . ($index + 1) . " for query: $query to user $chatId");
        }
    }

    private function fetchPageContent($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive'
        ]);
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/../cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/../cookies.txt');
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Logger::error("Failed to fetch URL: $url, HTTP Code: $httpCode");
            return false;
        }

        // ذخیره محتوا برای دیباگ
        $this->saveDebugContent($url, $response);
        return $response;
    }

    private function parseImageUrls($html, $count) {
        $doc = new DOMDocument();
        @$doc->loadHTML($html); // سرکوب هشدارهای HTML نامعتبر
        $xpath = new DOMXPath($doc);

        $imageUrls = [];
        // استخراج تصاویر با استفاده از XPath
        $nodes = $xpath->query('//img[@class="YQ4gaf"]/@src');
        foreach ($nodes as $node) {
            $url = $node->nodeValue;
            if (filter_var($url, FILTER_VALIDATE_URL) && strpos($url, 'http') === 0) {
                $imageUrls[] = $url;
                if (count($imageUrls) >= $count) {
                    break;
                }
            }
        }

        return $imageUrls;
    }

    private function saveDebugContent($url, $content) {
        $debugDir = __DIR__ . '/../debug/';
        if (!file_exists($debugDir)) {
            mkdir($debugDir, 0777, true);
        }
        $filename = $debugDir . 'search_' . md5($url) . '.html';
        file_put_contents($filename, $content);
        Logger::info("Saved debug content for URL: $url to $filename");
    }
}