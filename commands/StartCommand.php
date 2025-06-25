<?php
// commands/StartCommand.php

require_once __DIR__ . '/../helpers/Logger.php';

class StartCommand {
    private $bot;

    public function __construct($bot) {
        $this->bot = $bot;
    }

    public function execute($chatId, $userId, $text) {
        if (strpos($text, '/start') === 0) {
            Logger::info("Start command executed for chat $chatId by user $userId");
            $currentDateTime = date('Y-m-d H:i:s');
            $userCount = $this->getUserCount();
            $commands = $this->loadCommands();
            
            $message = "🌟 Welcome to the Telegram Bot!\n\n";
            $message .= "📅 Date & Time: $currentDateTime\n";
            $message .= "👥 Number of Users: $userCount\n\n";
            $message .= "Available Commands:\n";
            foreach ($commands as $command) {
                $commandName = str_replace('-', '', $command);
                $message .= "/$commandName\n";
            }
            
            $this->bot->sendMessage($chatId, $message);
        }
    }

    private function loadCommands() {
        $commandsDir = __DIR__;
        $commandFiles = scandir($commandsDir);
        $commands = [];
        foreach ($commandFiles as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && $file !== basename(__FILE__)) {
                $commandName = strtolower(pathinfo($file, PATHINFO_FILENAME));
                $commands[] = $commandName;
            }
        }
        return $commands;
    }
    
    private function getUserCount() {
        $userFile = __DIR__ . '/../users.txt';
        if (!file_exists($userFile)) {
            return 0;
        }
        $users = file($userFile, FILE_IGNORE_NEW_LINES);
        return count($users);
    }
}