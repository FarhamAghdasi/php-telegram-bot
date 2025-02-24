### Telegram Bot (AntiRip) Project

This project is a Telegram bot designed to manage various operations such as iframe extraction, theme searching, short link management, and more. It allows authorized users to execute different commands and retrieve required information.

---

### Project Structure

```
telegram-bot/
├── .gitignore
├── build/
│   ├── project.zip
│   └── unzip.php
├── commands/
│   ├── AddUserCommand.php
│   ├── clear-debug.php
│   ├── getiframe.php
│   ├── MyInfoCommand.php
│   ├── RemoveUserCommand.php
│   ├── shortlink.php
│   ├── start.php
│   ├── theme-info.php
│   └── themefinder.php
│   └── WebsiteDownloader.php
├── config/
│   └── config.php
├── core/
│   ├── Bot.php
│   ├── setWebhook.php
│   ├── ShortLinkService.php
│   └── Webhook.php
├── data/
│   └── short.php
├── helpers/
│   └── Logger.php
│   └── Loader.php
└── main.php
```

---

### Files & Directories Description

1. **`.gitignore`**: Git configuration file to exclude specific files like logs and debug folders.

2. **`build/`**: 
   - **`project.zip`**: Zipped project file.
   - **`unzip.php`**: PHP script for extracting ZIP files.

3. **`commands/`**: 
   - **`AddUserCommand.php`**: Command to add new users.
   - **`clear-debug.php`**: Command to clear debug folder.
   - **`getiframe.php`**: Command to extract iframe from URL.
   - **`MyInfoCommand.php`**: Command to retrieve user information.
   - **`RemoveUserCommand.php`**: Command to remove users.
   - **`shortlink.php`**: Command to generate short links.
   - **`start.php`**: Initial command to start the bot.
   - **`theme-info.php`**: Command to get theme information.
   - **`themefinder.php`**: Command to search for themes.

4. **`config/`**: 
   - **`config.php`**: Main configuration file containing bot token and authorized users.

5. **`core/`**: 
   - **`Bot.php`**: Main bot class handling Telegram API communication.
   - **`setWebhook.php`**: Script to configure Telegram webhook.
   - **`ShortLinkService.php`**: Service for managing short links.
   - **`Webhook.php`**: Webhook handler class.

6. **`data/`**: 
   - **`short.php`**: Short link management script.

7. **`helpers/`**: 
   - **`Logger.php`**: Logging utility for tracking events and errors.

8. **`main.php`**: Main entry point handling command execution.

---

### Bot Commands

- **`/start`**: Display welcome message and basic bot info.
- **`/adduser [User ID]`**: Add user to authorized list.
- **`/removeuser [User ID]`**: Remove user from authorized list.
- **`/getiframe [URL]`**: Extract iframe from specified URL.
- **`/themefinder [Theme Name]`**: Search for themes by name.
- **`/cleardebug`**: Clear debug folder.
- **`/shortlink [URL]`**: Create shortened URL.
- **`/themeinfo [URL]`**: Retrieve theme information.
- **`/myinfo`**: Display user information.

---

### Setup Instructions

1. **Initial Configuration**:
   - Update `config/config.php` with your bot token and authorized user IDs.

2. **Webhook Setup**:
   - Execute `core/setWebhook.php` to configure Telegram webhook.

3. **Launch Bot**:
   - Run `main.php` to start the bot.

---

### Requirements

- PHP 7.0+
- Web server (Apache/Nginx)
- Telegram bot token

---

### Important Notes

- Restrict access to sensitive files like `config.php`.
- Implement proper logging for error tracking.
- Maintain strict authorization controls.

---

### Contribution

Contributions are welcome! Please use Git workflow and submit pull requests.

---

### License

Released under MIT License. See `LICENSE` file.

---

### Sample Command Code

```php
// commands/StartCommand.php

require_once __DIR__ . '/../helpers/Logger.php';

class StartCommand {
    private $bot;
    
    public function __construct($bot) {
        $this->bot = $bot;
    }

    public function execute($chatId, $userId, $text) {
        if (!in_array((string)$userId, ALLOWED_USER_IDS)) {
            Logger::error("Access denied for user $userId.");
            $this->bot->sendMessage($chatId, "⚠️ Access denied");
            return;
        }

        if (strpos($text, '/start') === 0) {
            $currentDateTime = date('Y-m-d H:i:s');
            
            $commands = [
                '/getiframe [URL]' => 'Extract iframe URL',
                '/themefinder [Theme]' => 'Search themes',
                '/cleardebug' => 'Clear debug folder',
                '/start' => 'Show bot info'
            ];

            $userCount = $this->getUserCount();
            
            $message = "🌟 Welcome!\n\n";
            $message .= "📅 Current Time: $currentDateTime\n";
            $message .= "👥 Users: $userCount\n\n";
            $message .= "Available commands:\n";
            
            foreach ($commands as $command => $desc) {
                $message .= "$command - $desc\n";
            }
            
            $this->bot->sendMessage($chatId, $message);
        }
    }

    private function getUserCount() {
        $userFile = 'users.txt';
        return file_exists($userFile) ? count(file($userFile)) : 0;
    }
}
```
