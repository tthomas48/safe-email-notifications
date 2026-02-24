# Safe Email Notifications for FreeScout

Replace FreeScout user notification emails with minimal transactional messages. Instead of including the full message body (which can inadvertently forward spam and cause mail provider issues), notifications show only a short action description and a link into the app.

**Example:** "Jane Smith is requesting assistance" or "Jane Smith just replied to your conversation" with a link to view the conversation.

## Compatibility

- FreeScout 1.x
- Laravel 5.5

## Installation

This package is a [FreeScout-style module](https://github.com/LJPc-solutions/freescout-alternative-signatures-module) with `module.json`. Place it in FreeScout’s `Modules` directory **without** changing the root `composer.json`, so it survives container upgrades.

1. **Extract the release** (or clone) into your modules directory. If FreeScout uses a persistent `data/Modules` volume, use that:

   ```bash
   # Example: extract the GitHub release tarball
   tar -xzf safe-email-notifications-1.0.0.tar.gz -C /path/to/freescout/data/Modules
   mv data/Modules/safe-email-notifications-1.0.0 data/Modules/SafeEmailNotifications
   ```

   The folder name must be **`SafeEmailNotifications`** (StudlyCase) so it matches the `Modules\SafeEmailNotifications` namespace.

2. **Point FreeScout at that directory** so it scans for modules:
   - If the app’s `Modules` directory is **inside** the container and you want to use `data/Modules`, make the app’s `Modules` a symlink to `data/Modules` (e.g. in your image or entrypoint: `ln -sfn /data/Modules /var/www/html/Modules`), **or**
   - If your FreeScout install already uses `data/Modules` as the modules path, ensure this package lives at `data/Modules/SafeEmailNotifications`.

3. **Enable the module** in FreeScout: Settings → Modules → enable “Safe Email Notifications”.

4. Clear caches:

   ```bash
   php artisan config:clear
   php artisan view:clear
   php artisan cache:clear
   ```

No changes to the root `composer.json` are required; FreeScout’s existing `Modules\` PSR-4 mapping loads the module.

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
