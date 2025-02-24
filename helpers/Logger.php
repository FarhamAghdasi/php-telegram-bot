<?php
// helpers/Logger.php

class Logger {
    public static function log($message, $level = 'INFO') {
        $logFile = 'bot.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    public static function error($message) {
        self::log($message, 'ERROR');
    }

    public static function info($message) {
        self::log($message, 'INFO');
    }

    public static function debug($message) {
        self::log($message, 'DEBUG');
    }
}