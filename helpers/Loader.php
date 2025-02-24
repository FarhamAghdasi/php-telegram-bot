<?php
// helpers/Loader.php

class Loader {
    public static function loadCommands($bot, $chatId, $userId, $text) {
        $commandDir = __DIR__ . '/../commands/';
        $commandFiles = glob($commandDir . '*.php');

        foreach ($commandFiles as $file) {
            require_once $file;
            $className = pathinfo($file, PATHINFO_FILENAME);

            if (class_exists($className)) {
                $commandInstance = new $className($bot);
                if (method_exists($commandInstance, 'execute')) {
                    $commandInstance->execute($chatId, $userId, $text);
                }
            }
        }
    }
}
