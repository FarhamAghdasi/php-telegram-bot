<?php
// commands/PanelCommand.php

require_once __DIR__ . '/../helpers/Logger.php';

class PanelCommand {
    private $bot;
    private $projectRoot;
    private $userId;

    public function __construct($bot) {
        $this->bot = $bot;
        $this->projectRoot = realpath(__DIR__ . '/..');
    }

    public function execute($chatId, $userId, $text) {
        $this->userId = $userId;

        if (!in_array((string)$userId, ALLOWED_USER_IDS)) {
            Logger::error("Access denied for user $userId on /panel command.");
            $this->bot->sendMessage($chatId, "⚠️ Access denied: You are not authorized to use this command.");
            return;
        }

        if (strpos($text, '/panel') !== 0) {
            return;
        }

        $parts = explode(' ', $text, 3);
        $action = $parts[1] ?? '';

        switch (strtolower($action)) {
            case 'list':
                $this->listFiles($chatId);
                break;
            case 'read':
                if (isset($parts[2]) && !empty($parts[2])) {
                    $this->readFile($chatId, trim($parts[2]));
                } else {
                    $this->bot->sendMessage($chatId, "❌ Usage: /panel read [filename]");
                }
                break;
            case 'edit':
                if (count($parts) < 3 || empty($parts[2])) {
                    $this->bot->sendMessage($chatId, "❌ Usage: /panel edit [filename] [new_content]");
                } else {
                    $this->editFile($chatId, trim($parts[2]), $parts[3] ?? '');
                }
                break;
            default:
                $this->bot->sendMessage($chatId, "ℹ️ Usage:\n/panel list - Show all project files\n/panel read [filename] - Read file content\n/panel edit [filename] [new_content] - Edit file content");
        }
    }

    private function listFiles($chatId) {
        $files = [];
        $this->scanDirectory($this->projectRoot, $files);

        if (empty($files)) {
            $this->bot->sendMessage($chatId, "❌ No files found in the project.");
            return;
        }

        $message = "📂 Project Files:\n";
        foreach ($files as $file) {
            $relativePath = str_replace($this->projectRoot . '/', '', $file);
            $message .= "📄 $relativePath\n";
        }
        $this->bot->sendMessage($chatId, $message);
    }

    private function scanDirectory($dir, &$files) {
        $exclude = ['.env', 'config.php', 'build', '.git', '.github'];
        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            $relativePath = str_replace($this->projectRoot . '/', '', $path);

            if (in_array($relativePath, $exclude) || in_array($item, $exclude)) {
                continue;
            }

            if (is_dir($path)) {
                $this->scanDirectory($path, $files);
            } elseif (is_file($path)) {
                $files[] = $path;
            }
        }
    }

    private function readFile($chatId, $filename) {
        $filePath = $this->sanitizeFilePath($filename);
        if (!$filePath || !file_exists($filePath)) {
            $this->bot->sendMessage($chatId, "❌ File '$filename' does not exist.");
            return;
        }

        if ($this->isRestrictedFile($filePath)) {
            Logger::error("Attempt to read restricted file '$filename' by user {$this->userId}.");
            $this->bot->sendMessage($chatId, "⚠️ Access denied: This file is restricted.");
            return;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            Logger::error("Failed to read file '$filename'.");
            $this->bot->sendMessage($chatId, "❌ Failed to read file '$filename'.");
            return;
        }

        $content = substr($content, 0, 4000); // Telegram message limit
        $content = htmlspecialchars($content);
        $message = "📜 Content of '$filename':\n<code>$content</code>";
        $this->bot->sendMessage($chatId, $message, 'HTML');
    }

    private function editFile($chatId, $filename, $newContent) {
        $filePath = $this->sanitizeFilePath($filename);
        if (!$filePath || !file_exists($filePath)) {
            $this->bot->sendMessage($chatId, "❌ File '$filename' does not exist.");
            return;
        }

        if ($this->isRestrictedFile($filePath)) {
            Logger::error("Attempt to edit restricted file '$filename' by user {$this->userId}.");
            $this->bot->sendMessage($chatId, "⚠️ Access denied: This file is restricted.");
            return;
        }

        if (empty($newContent)) {
            $this->bot->sendMessage($chatId, "❌ New content cannot be empty.");
            return;
        }

        $backupDir = $this->projectRoot . '/backup';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        $backupFile = $backupDir . '/' . basename($filename) . '.' . date('Ymd_His') . '.bak';
        if (!copy($filePath, $backupFile)) {
            Logger::error("Failed to create backup for '$filename'.");
            $this->bot->sendMessage($chatId, "❌ Failed to create backup for '$filename'.");
            return;
        }

        if (file_put_contents($filePath, $newContent) === false) {
            Logger::error("Failed to write to file '$filename'.");
            $this->bot->sendMessage($chatId, "❌ Failed to write to file '$filename'.");
            return;
        }

        Logger::info("File '$filename' edited successfully by user {$this->userId}.");
        $this->bot->sendMessage($chatId, "✅ File '$filename' edited successfully. Backup created at: $backupFile");
    }

    private function sanitizeFilePath($filename) {
        $filePath = realpath($this->projectRoot . '/' . $filename);
        if ($filePath === false || strpos($filePath, $this->projectRoot) !== 0) {
            return false;
        }
        return $filePath;
    }

    private function isRestrictedFile($filePath) {
        $restrictedFiles = ['.env', 'config.php'];
        $relativePath = str_replace($this->projectRoot . '/', '', $filePath);
        return in_array(basename($relativePath), $restrictedFiles) || strpos($relativePath, '.git') === 0 || strpos($relativePath, '.github') === 0;
    }
}
