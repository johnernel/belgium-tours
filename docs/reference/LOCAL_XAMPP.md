# Run locally on XAMPP (Windows)

## What was checked on this machine
- XAMPP PHP 8.5.12 at `C:\xampp\php`. Laravel 12 installs and runs on it (needs PHP 8.2 or newer).
- Composer 2.11, Node 26, npm 11 installed.
- Extensions enabled in `C:\xampp\php\php.ini` (a backup is at `php.ini.bak-claude`): `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `curl`, `gd`, `intl`, `zip`. Test suite also uses `pdo_sqlite`.
- Config is a one-line-per-extension change: remove the leading `;` from `extension=gd`, `extension=intl`, `extension=zip`, then restart Apache/CLI.

## One-time setup
1. Start **MySQL** (and Apache if you want the vhost) in the XAMPP control panel.
2. Create the database. In phpMyAdmin (http://localhost/phpmyadmin) run:
   ```sql
   CREATE DATABASE gobelgium CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   XAMPP defaults (user `root`, empty password, `127.0.0.1:3306`) are already in `.env.example`. For a dedicated user instead:
   `CREATE USER 'gobelgium'@'localhost' IDENTIFIED BY 'choose-a-password'; GRANT ALL ON gobelgium.* TO 'gobelgium'@'localhost';` and set `DB_USERNAME` / `DB_PASSWORD` in `.env`.
3. From the project folder:
   ```
   copy .env.example .env
   composer install
   npm install
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   npm run build
   ```
4. Seeding creates the admin from `ADMIN_SEED_EMAIL` / `ADMIN_SEED_PASSWORD`. Change the password in Admin > Admin users.

## Two ways to view the site
**a) `php artisan serve`** (simplest): http://127.0.0.1:8000. Use `npm run dev` in a second terminal for live CSS/JS editing (stop it and run `npm run build` before testing performance).
If `artisan serve` fails on Windows: `php -S 127.0.0.1:8000 -t public vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php` (run from the project root).

**b) Apache virtual host** such as `gobelgium.test`:
1. Add to `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
   ```apache
   <VirtualHost *:80>
       ServerName gobelgium.test
       DocumentRoot "C:/Websites/public"
       <Directory "C:/Websites/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
   Make sure `Include conf/extra/httpd-vhosts.conf` is not commented out in `httpd.conf` and `mod_rewrite` is enabled.
2. As Administrator, add `127.0.0.1 gobelgium.test` to `C:\Windows\System32\drivers\etc\hosts`.
3. Set `APP_URL=http://gobelgium.test` in `.env`, restart Apache.
(This was documented, not applied: editing the hosts file needs administrator rights and restarting your Apache would interrupt other sites.)

## Local services
- **Mail:** `MAIL_MAILER=log`. Enquiry emails are written to `storage/logs/laravel.log`. To see real inboxes use Mailpit (`MAIL_MAILER=smtp`, host `127.0.0.1`, port `1025`). Set "Send new-enquiry emails to" in Admin > Settings or no email is sent.
- **reCAPTCHA:** `.env.example` holds Google's public **test** keys, so every form can be submitted locally. Use real keys in production.
- **Queue:** `QUEUE_CONNECTION=sync` locally (jobs run inline). Production uses `database` plus a worker (see DEPLOYMENT.md).
- **GoHighLevel:** off until `GHL_API_TOKEN` and `GHL_LOCATION_ID` are filled in `.env` (see GHL_INTEGRATION.md).

## Troubleshooting
- **Port 80 in use** (Skype, IIS, another Apache): stop the other service, or use `php artisan serve` instead.
- **"could not find driver" / missing extension:** enable it in `php.ini` (see above), restart, check with `php -m`.
- **Storage permission or "Failed to open stream":** run `php artisan storage:link`; make sure `storage` and `bootstrap/cache` are writable.
- **419 Page Expired:** the session cookie or CSRF token is stale. Clear cookies, check `APP_URL` matches the address in the browser, and run `php artisan optimize:clear`.
- **Unstyled page / missing assets:** run `npm run build` (or `npm run dev`).
- **Changed `.env` but nothing happens:** `php artisan optimize:clear`.
- **Tests wiping your database:** never run tests with a cached config; run `php artisan optimize:clear` first.
