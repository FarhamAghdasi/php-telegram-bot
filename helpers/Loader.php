<?php
// helpers/Loader.php

require_once __DIR__ . '/Logger.php';

class Loader {
    public static function loadCommands($bot, $chatId, $userId, $text) {
        $commandDir = realpath(__DIR__ . '/../commands') . '/';
        $commandFiles = glob($commandDir . '*.php');

        Logger::info("Starting to load commands from directory: $commandDir");

        foreach ($commandFiles as $file) {
            Logger::info("Attempting to load command file: $file");
            try {
                require_once $file;
                $className = pathinfo($file, PATHINFO_FILENAME);

                if (class_exists($className)) {
                    Logger::info("Class $className found, creating instance");
                    $commandInstance = new $className($bot);
                    if (method_exists($commandInstance, 'execute')) {
                        Logger::info("Executing command: $className for user $userId with text: $text");
                        $commandInstance->execute($chatId, $userId, $text);
                    } else {
                        Logger::error("Method 'execute' not found in class $className");
                    }
                } else {
                    Logger::error("Class $className not found in file $file");
                }
            } catch (Exception $e) {
                Logger::error("Error loading command file $file: " . $e->getMessage());
            }
        }

        Logger::info("Finished loading commands");
    }
}