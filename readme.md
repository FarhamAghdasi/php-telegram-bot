### پروژه Telegram Bot (AntiRip)

این پروژه یک ربات تلگرام است که برای مدیریت و انجام عملیات‌های مختلف مانند استخراج iframe، جستجوی تم، مدیریت لینک‌های کوتاه و غیره طراحی شده است. این ربات به کاربران مجاز اجازه می‌دهد تا از دستورات مختلف استفاده کنند و اطلاعات مورد نیاز خود را دریافت کنند.

---

### ساختار پروژه

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
|   └── WebsiteDownloader.php
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
|   └── Loader.php
└── main.php
```

---

### توضیحات فایل‌ها و پوشه‌ها

1. **`.gitignore`**: فایل تنظیمات Git برای نادیده گرفتن فایل‌های خاص مانند فایل‌های لاگ و پوشه‌های دیباگ.

2. **`build/`**: 
   - **`project.zip`**: فایل فشرده پروژه.
   - **`unzip.php`**: اسکریپت PHP برای استخراج فایل‌های ZIP.

3. **`commands/`**: 
   - **`AddUserCommand.php`**: دستور اضافه کردن کاربر جدید.
   - **`clear-debug.php`**: دستور پاک کردن پوشه دیباگ.
   - **`getiframe.php`**: دستور استخراج iframe از یک URL.
   - **`MyInfoCommand.php`**: دستور دریافت اطلاعات کاربر.
   - **`RemoveUserCommand.php`**: دستور حذف کاربر.
   - **`shortlink.php`**: دستور ایجاد لینک کوتاه.
   - **`start.php`**: دستور شروع ربات و نمایش اطلاعات اولیه.
   - **`theme-info.php`**: دستور دریافت اطلاعات تم.
   - **`themefinder.php`**: دستور جستجوی تم.

4. **`config/`**: 
   - **`config.php`**: فایل تنظیمات اصلی ربات شامل توکن ربات و لیست کاربران مجاز.

5. **`core/`**: 
   - **`Bot.php`**: کلاس اصلی ربات برای ارسال پیام و مدیریت ارتباط با تلگرام.
   - **`setWebhook.php`**: اسکریپت تنظیم وب‌هوک برای ربات.
   - **`ShortLinkService.php`**: سرویس مدیریت لینک‌های کوتاه.
   - **`Webhook.php`**: کلاس مدیریت وب‌هوک.

6. **`data/`**: 
   - **`short.php`**: اسکریپت مدیریت لینک‌های کوتاه.

7. **`helpers/`**: 
   - **`Logger.php`**: کلاس لاگ‌گیری برای ثبت رویدادها و خطاها.

8. **`main.php`**: فایل اصلی اجرای ربات که تمام دستورات را مدیریت می‌کند.

---

### دستورات ربات

- **`/start`**: نمایش پیام خوش‌آمدگویی و اطلاعات اولیه ربات.
- **`/adduser [User ID]`**: اضافه کردن کاربر جدید به لیست کاربران مجاز.
- **`/removeuser [User ID]`**: حذف کاربر از لیست کاربران مجاز.
- **`/getiframe [URL]`**: استخراج iframe از یک URL.
- **`/themefinder [Theme Name]`**: جستجوی تم بر اساس نام.
- **`/cleardebug`**: پاک کردن پوشه دیباگ.
- **`/shortlink [URL]`**: ایجاد لینک کوتاه از یک URL.
- **`/themeinfo [URL]`**: دریافت اطلاعات تم از یک URL.
- **`/myinfo`**: دریافت اطلاعات کاربر.

---

### نحوه اجرا

1. **تنظیمات اولیه**:
   - فایل `config/config.php` را باز کرده و توکن ربات و لیست کاربران مجاز را تنظیم کنید.

2. **تنظیم وب‌هوک**:
   - فایل `core/setWebhook.php` را اجرا کنید تا وب‌هوک ربات تنظیم شود.

3. **اجرای ربات**:
   - فایل `main.php` را اجرا کنید تا ربات شروع به کار کند.

---

### نیازمندی‌ها

- PHP 7.0 یا بالاتر
- دسترسی به سرور وب (مانند Apache یا Nginx)
- توکن ربات تلگرام

---

### نکات مهم

- اطمینان حاصل کنید که فایل‌های حساس مانند `config.php` در دسترس عموم قرار نگیرند.
- از لاگ‌گیری مناسب برای ردیابی خطاها و رویدادها استفاده کنید.
- برای افزایش امنیت، دسترسی به ربات را فقط به کاربران مجاز محدود کنید.

---

### توسعه و مشارکت

اگر می‌خواهید در توسعه این پروژه مشارکت کنید، لطفاً از دستورات Git استفاده کنید و تغییرات خود را به صورت Pull Request ارسال کنید.

---

### لایسنس

این پروژه تحت لایسنس MIT منتشر شده است. برای اطلاعات بیشتر به فایل `LICENSE` مراجعه کنید.


### کد های نمونه بخش کامند ها

<?php
// commands/StartCommand.php

require_once __DIR__ . '/../helpers/Logger.php';

class StartCommand {
    private $bot;
    
    // Constructor to initialize bot object
    public function __construct($bot) {
        $this->bot = $bot;
    }

    public function execute($chatId, $userId, $text) {
        // Ensure the user is  (optional, can be removed if needed)
        if (!in_array((string)$userId, ALLOWED_USER_IDS)) {
            Logger::error("Access denied for user $userId.");
            $this->bot->sendMessage($chatId, "⚠️ Access denied: You are not authorized to use this bot.");
            return;
        }

        // Check if the command is /start
        if (strpos($text, '/start') === 0) {
            // Fetch the current date and time
            $currentDateTime = date('Y-m-d H:i:s');

            // List of available commands
            $commands = [
                '/getiframe [URL]' => 'Extract iframe URL from the provided website URL',
                '/themefinder [Theme Name]' => 'Search for a theme on RTL Theme website',
                '/cleardebug' => 'Clear the debug folder',
                '/start' => 'Display the start message with bot info'
            ];

            // Count the number of users (you can manage this with a simple array or database)
            // Assuming we store user IDs in a file or database, you can replace this with actual logic
            $userCount = $this->getUserCount();

            // Prepare the message
            $message = "🌟 Welcome to the Telegram Bot!\n\n";
            $message .= "📅 Current Date & Time: $currentDateTime\n";
            $message .= "👥 Number of Users: $userCount\n\n";
            $message .= "Here are the available commands:\n";
            foreach ($commands as $command => $description) {
                $message .= "$command - $description\n";
            }

            // Send the message to the user
            $this->bot->sendMessage($chatId, $message);
        }
    }

    // Function to get the number of users (this is just an example, modify as needed)
    private function getUserCount() {
        // Here we assume that user IDs are saved in a file 'users.txt'. Replace with actual database logic if necessary.
        $userFile = 'users.txt';

        if (!file_exists($userFile)) {
            return 0;
        }

        // Read all user IDs from the file
        $users = file($userFile, FILE_IGNORE_NEW_LINES);
        return count($users);
    }
}
