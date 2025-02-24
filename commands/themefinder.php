<?php
// commands/ThemeFinderCommand.php

require_once __DIR__ . '/../helpers/Logger.php';

class ThemeFinderCommand {
    private $bot;

    public function __construct($bot) {
        $this->bot = $bot;
    }

    public function execute($chatId, $userId, $text) {
        // Ensure the user is authorized
        if (!in_array((string)$userId, ALLOWED_USER_IDS)) {
            Logger::error("Access denied for user $userId.");
            $this->bot->sendMessage($chatId, "⚠️ Access denied: You are not authorized to use this bot.");
            return;
        }
        

        // Check if the command is /themefinder
        if (strpos($text, '/themefinder') === 0) {
            $parts = explode(' ', $text, 2);

            // Ensure a theme name is provided
            if (count($parts) < 2 || empty($parts[1])) {
                $this->bot->sendMessage($chatId, "❌ Usage: /themefinder [Name Theme]\nPlease provide a theme name.");
                return;
            }

            $themeName = trim($parts[1]);

            // Call the function to find the theme
            $result = $this->findTheme($themeName);

            if ($result['found']) {
                $this->bot->sendMessage($chatId, "✅ Theme found! You can view it at: " . $result['url']);
            } else {
                $this->bot->sendMessage($chatId, "❌ No theme found for '$themeName'.");
            }
        }
    }

    // Function to find the theme on RTL Theme website
    private function findTheme($themeName) {
        $url = 'https://www.rtl-theme.com/?s=' . urlencode($themeName);
        
        // Get the content of the search page
        $htmlContent = $this->fetchPageContent($url);

        // Parse the HTML content and check for the theme
        $themeFound = $this->parseHtmlContent($htmlContent, $themeName);

        return $themeFound;
    }

// Function to parse the HTML content and check if the theme exists
private function parseHtmlContent($htmlContent, $themeName) {
    // Load the HTML content into DOMDocument
    $doc = new DOMDocument();
    @$doc->loadHTML($htmlContent); // Suppress warnings for invalid HTML

    // Check if the title contains the "not found" message
    $titleElements = $doc->getElementsByTagName('h1');
    foreach ($titleElements as $element) {
        if ($element->getAttribute('class') === 'title') {
            // Get the text content of the <a> tag inside <h1>
            $linkText = $element->nodeValue;

            // Convert both link text and theme name to lowercase for case-insensitive comparison
            if (stripos($linkText, $themeName) !== false) {
                // Theme found, return the URL from the <a> tag
                return ['found' => true, 'url' => $element->getElementsByTagName('a')[0]->getAttribute('href')];
            }
        }
    }

    // If no theme is found in <h1 class="title">, check for "no product found"
    if (strpos($htmlContent, 'متاسفانه محصولی یافت نشد!') !== false) {
        return ['found' => false, 'url' => '']; // Theme not found
    }

    return ['found' => false, 'url' => '']; // Theme not found
}


    // Function to fetch the content of the page
    private function fetchPageContent($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive'
        ]);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
        $response = curl_exec($ch);
        curl_close($ch);

        // Save the content for debugging
        $this->saveDebugContent($url, $response);

        return $response;
    }

    // Function to save page content for debugging purposes
    private function saveDebugContent($url, $content) {
        $debugDir = 'debug/';
        if (!file_exists($debugDir)) {
            mkdir($debugDir, 0777, true); // Create the directory if it doesn't exist
        }
        $filename = $debugDir . md5($url) . '.html'; // Use the URL as a unique filename
        file_put_contents($filename, $content);
    }
}
