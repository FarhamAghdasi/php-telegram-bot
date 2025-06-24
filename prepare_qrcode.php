<?php
require 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$outputDir = __DIR__ . '/libs/qr';

@mkdir($outputDir, 0777, true);

$filesToCopy = [
    '/vendor/endroid/qr-code' => $outputDir
];

foreach ($filesToCopy as $src => $dest) {
    $src = __DIR__ . $src;
    $dest = $dest;

    if (!is_dir($src)) {
        echo "Missing: $src\n";
        continue;
    }

    shell_exec("cp -r $src $dest");
}

echo "✅ QR Code classes copied to libs/qr\n";
