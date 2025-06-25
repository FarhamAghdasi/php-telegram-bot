<?php
// check_requirements.php

echo "🔍 Checking PHP Telegram Bot Requirements...\n\n";

// 1. بررسی نسخه PHP
$requiredPhpVersion = '7.0';
$currentPhpVersion = phpversion();
echo "PHP Version:\n";
echo "  - Required: $requiredPhpVersion or higher\n";
echo "  - Current: $currentPhpVersion\n";
echo version_compare($currentPhpVersion, $requiredPhpVersion, '>=') ? "  ✅ OK\n" : "  ❌ PHP version is too low!\n";
echo "\n";

// 2. بررسی افزونه‌های PHP
$requiredExtensions = ['curl', 'mbstring', 'gd', 'dom', 'fileinfo', 'json'];
echo "PHP Extensions:\n";
foreach ($requiredExtensions as $extension) {
    echo "  - $extension: ";
    if (extension_loaded($extension)) {
        echo "✅ Loaded\n";
    } else {
        echo "❌ Not loaded\n";
    }
}
echo "\n";

// 3. بررسی وجود کتابخانه endroid/qr-code
echo "Composer Dependencies:\n";
$composerAutoload = __DIR__ . '/vendor/autoload.php';
echo "  - endroid/qr-code: ";
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
    if (class_exists('Endroid\QrCode\QrCode')) {
        echo "✅ Installed\n";
    } else {
        echo "❌ Not installed (run 'composer require endroid/qr-code')\n";
    }
} else {
    echo "❌ Composer autoload not found (run 'composer install')\n";
}
echo "\n";

// 4. بررسی دسترسی‌های نوشتن به پوشه‌ها
$requiredDirs = ['data', 'debug', 'stickers'];
echo "Directory Write Permissions:\n";
foreach ($requiredDirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    echo "  - $dir: ";
    if (is_dir($fullPath)) {
        if (is_writable($fullPath)) {
            echo "✅ Writable\n";
        } else {
            echo "❌ Not writable (check permissions)\n";
        }
    } else {
        echo "❌ Directory does not exist\n";
    }
}
echo "\n";

// 5. بررسی وجود فایل‌های فونت
$requiredFonts = ['IranNastaliq.ttf', 'sahel.ttf', 'Vazir.ttf'];
echo "Font Files:\n";
$fontsDir = __DIR__ . '/fonts/';
foreach ($requiredFonts as $font) {
    $fontPath = $fontsDir . $font;
    echo "  - $font: ";
    if (file_exists($fontPath)) {
        if (is_readable($fontPath)) {
            echo "✅ Found and readable\n";
        } else {
            echo "❌ Found but not readable (check permissions)\n";
        }
    } else {
        echo "❌ Not found\n";
    }
}
echo "\n";

// 6. بررسی اتصال به دیتابیس
echo "Database Connection:\n";
require_once __DIR__ . '/config/database.php';
if (isset($GLOBALS['pdo'])) {
    try {
        $GLOBALS['pdo']->query('SELECT 1');
        echo "  - MySQL Connection: ✅ Connected\n";
    } catch (PDOException $e) {
        echo "  - MySQL Connection: ❌ Failed - " . $e->getMessage() . "\n";
    }
} else {
    echo "  - MySQL Connection: ❌ PDO not initialized\n";
}
echo "\n";

// 7. بررسی دسترسی به Webhook URL
echo "Webhook URL Accessibility:\n";
require_once __DIR__ . '/config/config.php';
$webhookUrl = getenv('WEBHOOK_URL');
echo "  - Webhook URL: $webhookUrl\n";
$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "  - Status: ";
echo ($httpCode >= 200 && $httpCode < 300) ? "✅ Accessible (HTTP $httpCode)\n" : "❌ Not accessible (HTTP $httpCode)\n";
echo "\n";

echo "✅ Check completed. Review the results above for any issues.\n";