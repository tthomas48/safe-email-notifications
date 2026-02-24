# Safe Email Notifications for FreeScout

Replace FreeScout user notification emails with minimal transactional messages. Instead of including the full message body (which can inadvertently forward spam and cause mail provider issues), notifications show only a short action description and a link into the app.

**Example:** "Jane Smith is requesting assistance" or "Jane Smith just replied to your conversation" with a link to view the conversation.

## Compatibility

- FreeScout 1.x
- Laravel 5.5

## Installation

```bash
composer require yourname/safe-email-notifications
```

Laravel will auto-discover the service provider. Clear caches if needed:

```bash
php artisan config:clear
php artisan view:clear
```

## Features

- **Transactional subjects:** e.g. `[#42] Jane Smith is requesting assistance` instead of `[#42] Original email subject`
- **No message body:** Only action summary + link (avoids spam forwarding)
- **Same styling:** Keeps FreeScout's notification layout and branding
- **Custom translations:** Override strings via published lang files

## Customizing Translations

Publish the language files to customize notification copy:

```bash
php artisan vendor:publish --tag=safe-email-notifications-lang
```

Edit `lang/en/messages.php` (or your locale) to change the action strings.

## Running Tests

Unit tests are framework-agnostic (no Laravel or FreeScout required):

```bash
composer test
```

- **SubjectBuilder** and **SubjectResult** are unit-tested with plain PHP objects and PHPUnit 9.5.
- **Blade templates** are checked for structure (no message body, presence of “View conversation” copy). Full rendering is exercised in FreeScout.
- Requires PHP 7.1+ and the usual PHPUnit extensions (e.g. `dom`, `xml`).

## License

MIT
