<?php
class Maintenance {
    public static function check() {
        if (MAINTENANCE_MODE) {
            $content = json_decode(file_get_contents("php://input"), true);
            $chatId = $content['message']['chat']['id'] ?? null;
            
            if ($chatId) {
                $bot = new Bot(BOT_TOKEN);
                $bot->sendMessage($chatId, "🔧 ربات در حال تعمیرات است. لطفاً بعداً مراجعه کنید.");
                
                // اطلاع به ادمین
                if ($chatId != ALLOWED_USER_IDS) {
                    $bot->sendMessage(ALLOWED_USER_IDS, "⚠️ کاربر تلاش کرد به ربات دسترسی پیدا کند:\nآیدی: $chatId");
                }
            }
            exit;
        }
    }
}