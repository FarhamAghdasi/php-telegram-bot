<?php
// commands/ClearDebugCommand.php

require_once __DIR__ . '/../helpers/Logger.php';

class ClearDebugCommand {
    private $bot;

    public function __construct($bot) {
        $this->bot = $bot;
    }

    public function execute($chatId, $userId, $text) {
        if (!in_array((string)$userId, ALLOWED_USER_IDS)) {
            Logger::error("Access denied for user $userId.");
            $this->bot->sendMessage($chatId, "⚠️ Access denied: You are not authorized to use this bot.");
            return;
        }
        // Check if the command is /cleardebug
        if (strpos($text, '/cleardebug') === 0) {
            Logger::info("User $userId is trying to clear the debug folder.");
            // Call the function to clear the debug folder
            $result = $this->clearDebugFolder();

            if ($result) {
                Logger::info("Debug folder cleared successfully by user $userId.");
                $this->bot->sendMessage($chatId, "✅ Debug folder cleared successfully.");
            } else {
                Logger::error("Failed to clear the debug folder by user $userId.");
                $this->bot->sendMessage($chatId, "❌ Failed to clear the debug folder.");
            }
        }
    }

    // Function to clear the debug folder
    private function clearDebugFolder() {
        $debugDir = 'debug/';
        
        // Check if the folder exists
        if (!is_dir($debugDir)) {
            return false; // Folder doesn't exist
        }

        // Get all files in the debug folder
        $files = array_diff(scandir($debugDir), array('.', '..'));

        // Iterate over each file and delete it
        foreach ($files as $file) {
            $filePath = $debugDir . $file;
            if (is_file($filePath)) {
                unlink($filePath); // Delete the file
            }
        }

        return true;
    }
}