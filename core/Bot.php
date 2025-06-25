<?php
// core/Bot.php

class Bot {
    private $token;

    public function __construct($token) {
        $this->token = $token;
    }

    // ارسال پیام
    public function sendMessage($chatId, $text, $parseMode = 'HTML') {
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    // دریافت اطلاعات فایل
    public function getFile($fileId) {
        $url = "https://api.telegram.org/bot{$this->token}/getFile";
        $data = ['file_id' => $fileId];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    // ارسال عکس
    public function sendPhoto($chatId, $photoUrl, $caption = '', $parseMode = 'HTML') {
        $url = "https://api.telegram.org/bot{$this->token}/sendPhoto";
        $data = [
            'chat_id' => $chatId,
            'photo' => $photoUrl,
            'caption' => $caption,
            'parse_mode' => $parseMode
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function getUserProfilePhotos($userId, $offset = 0, $limit = 1) {
        $url = "https://api.telegram.org/bot{$this->token}/getUserProfilePhotos";
        $data = [
            'user_id' => $userId,
            'offset' => $offset,
            'limit' => $limit
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    
        return json_decode($response, true);
    }
}