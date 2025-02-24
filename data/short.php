<?php
// short.php

require_once '../core/ShortLinkService.php'; 

if (isset($_GET['short'])) {
    $shortCode = $_GET['short'];
    
    $shortLinkService = new ShortLinkService();
    
    $originalUrl = $shortLinkService->getOriginalUrl($shortCode);

    if ($originalUrl) {
        $shortLinkService->incrementVisit($shortCode);
        
        header("Location: $originalUrl");
        exit;
    } else {
        echo "لینک کوتاه نامعتبر است.";
    }
} else {
    echo "لینک کوتاه موجود نیست.";
}
