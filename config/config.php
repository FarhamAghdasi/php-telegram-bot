<?php
// config/config.php

function loadEnvFile($file) {
    if (!file_exists($file)) return;

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

loadEnvFile(__DIR__ . '/../.env');

define('BOT_TOKEN', getenv('BOT_TOKEN'));
define('ALLOWED_USER_IDS', explode(',', getenv('ALLOWED_USER_IDS')));

define('MAINTENANCE_MODE', getenv('MAINTENANCE_MODE') === 'true');
define('IFRAME_EXTRACTOR_URL', getenv('IFRAME_EXTRACTOR_URL'));
define('REQUIRED_CHANNELS', ['-']);
define('CREATOR_NAME', getenv('CREATOR_NAME'));
define('CREATOR_INSTAGRAM', getenv('CREATOR_INSTAGRAM'));
define('CREATOR_WEBSITE', getenv('CREATOR_WEBSITE'));
define('COPYRIGHT_TEXT', getenv('COPYRIGHT_TEXT'));
