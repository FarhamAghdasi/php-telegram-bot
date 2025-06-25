<?php
// commands/ThemeInfoCommand.php

require_once __DIR__ . '/../helpers/Logger.php';


// use DOMDocument;
// use DOMXPath;

class ThemeInfoCommand {
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
        

        if (strpos($text, '/themeinfo') === 0) {
            $parts = explode(' ', $text, 2);

            if (count($parts) < 2 || empty($parts[1])) {
                $this->bot->sendMessage($chatId, "❌ Usage: /themeinfo [URL]");
                return;
            }

            $url = trim($parts[1]);
            $siteType = $this->identifySite($url);
            if (!$siteType) {
                $this->bot->sendMessage($chatId, "❌ Unsupported URL format.");
                return;
            }

            $htmlContent = $this->fetchHtmlContent($url);
            if (!$htmlContent) {
                $this->bot->sendMessage($chatId, "❌ Failed to fetch content from the URL.");
                return;
            }

            // Save HTML for debugging
            $this->saveHtmlDebug($htmlContent);

            $data = $this->extractData($htmlContent, $siteType);

            $response = "✅ Site Type: $siteType\n";
            foreach ($data as $key => $value) {
                $response .= "🔹 $key: $value\n";
            }

            $this->bot->sendMessage($chatId, $response);
        }
    }

    private function identifySite($url) {
        if (preg_match('/^https?:\/\/themeforest\.net\/item\/[a-z0-9\-]+\/\d+$/', $url)) {
            return 'ThemeForest';
        } elseif (preg_match('/^https?:\/\/www\.rtl\-theme\.com\/[a-z0-9\-]+\/$/', $url)) {
            return 'RTL';
        }
        return null;
    }

    private function fetchHtmlContent($url) {
        $cookieFile = __DIR__ . '/../cookies.txt';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }

    private function saveHtmlDebug($htmlContent) {
        $debugDir = __DIR__ . '/../debug';
        if (!is_dir($debugDir)) {
            mkdir($debugDir, 0777, true);
        }

        $fileName = $debugDir . '/debug_' . uniqid() . '.html';
        file_put_contents($fileName, $htmlContent);
    }

    private function extractData($html, $siteType) {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $data = [];
        $selectors = $this->getSelectors($siteType);

        foreach ($selectors as $key => $selector) {
            try {
                $nodes = null;
                if (strpos($selector, '//') === 0) {
                    $nodes = $xpath->query($selector);
                } else {
                    $xpathQuery = $this->cssToXPath($selector);
                    $nodes = $xpath->query($xpathQuery);
                }

                if ($nodes && $nodes->length > 0) {
                    $values = [];
                    foreach ($nodes as $node) {
                        $values[] = trim($node->textContent);
                    }
                    $data[$key] = implode(', ', $values);
                } else {
                    $data[$key] = "❌ Not Found (Selector: $selector)";
                }
            } catch (Exception $e) {
                $data[$key] = "❌ Error: " . $e->getMessage();
            }
        }

        return $data;
    }

    private function getSelectors($siteType) {
    if ($siteType === 'RTL') {
        return [
            'Title' => '//h1[@class="title"]', // عنوان محصول
            'Sales' => '//div[contains(@class, "detail-item sale")]//span[@class="count"]', // تعداد فروش
            'SatisfactionRate' => '//div[contains(@class, "detail-item rate")]//span[@class="count"]', // رضایت مشتری
            'Price' => '//div[@class="price"]//ins[@class="sale"]', // قیمت
            'ProductImage' => '//div[contains(@class, "product-thumbnail")]//img/@data-src', // لینک تصویر محصول
            'Version' => '//table[@class="attributes-table"]//td[text()="ورژن"]/following-sibling::td', // ورژن محصول
            'LastUpdate' => '//table[@class="attributes-table"]//td[text()="تاریخ به‌روزرسانی"]/following-sibling::td', // تاریخ به‌روزرسانی
            'PublishedDate' => '//table[@class="attributes-table"]//td[text()="تاریخ انتشار"]/following-sibling::td', // تاریخ انتشار
            'Category' => '//table[@class="attributes-table"]//td[text()="دسته‌بندی اصلی"]/following-sibling::td/a', // دسته‌بندی اصلی
            'AuthorName' => '//h4[@class="title"]/a', // نام نویسنده
            'AuthorLink' => '//h4[@class="title"]/a/@href', // لینک نویسنده
            'ProductDescription' => '//meta[@name="description"]/@content', // توضیحات محصول از meta tag
            'ProductArchive' => '//div[@class="product-archive"]/span', // توضیحات اضافی محصول
            'Features' => '//ul/li/@title',
        ];
    } elseif ($siteType === 'ThemeForest') {
        return [
            'Title' => '//h1[@class="t-heading -color-inherit -size-l h-m0 is-hidden-phone"]',
            'SalesCount' => '//div[@class="item-header__sales-count"]//strong',
            'EliteAuthor' => '//p[contains(@class, "t-body -size-m h-m0") and text()="Elite Author"]',
            'Author' => '//h2[@class="t-heading -size-s h-text-overflow-wrap-anywhere"]//a',
            'LastUpdate' => '//tr[@class="js-condense-item-page-info-panel--last_update"]//time',
            'PublishedDate' => '//tr//td[text()="Published"]/following-sibling::td/span',
            'PreviewImage' => '//meta[@property="og:image"]/@content', // مقدار content
            'IncludedFiles' => '//tr[td[@class="meta-attributes__attr-name" and contains(text(), "ThemeForest Files Included")]]//td[@class="meta-attributes__attr-detail"]/a',
            'Price' => '//b[@class="t-currency"]/span[@class="js-purchase-price"]',
        ];
    }

    return [];
}


    private function cssToXPath($cssSelector) {
        $cssSelector = str_replace('.', '[@class="', $cssSelector);
        $cssSelector = str_replace('#', '[@id="', $cssSelector);
        $cssSelector = str_replace(' ', '/', $cssSelector);
        return "//" . rtrim($cssSelector, '"') . "]";
    }
}
