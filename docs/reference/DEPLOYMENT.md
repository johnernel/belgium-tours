# Deploying to Hostinger (shared hosting)

Hostinger shared plans run PHP and MySQL with hPanel. Steps below assume a Premium/Business plan with a domain attached. SSH is available on Business and above; the notes cover the no-SSH route too.

## 1. Prepare locally
1. `composer install --no-dev --optimize-autoloader`
2. `npm ci && npm run build` (assets are built locally; the server does not need Node).
3. Do not upload `node_modules`, `.git`, `.env`, `tests`.

## 2. Hostinger setup (hPanel)
1. **PHP version:** Advanced > PHP Configuration, choose 8.2 or newer. Make sure `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `curl`, `gd`, `intl`, `zip`, `exif` are ticked.
2. **Database:** Databases > MySQL Databases, create a database and user; note the names (Hostinger prefixes them).
3. **SSL:** Security > SSL, install the free certificate and force HTTPS.

## 3. Upload
Keep the application **outside** the web root.
- Upload the project to `/home/USER/gobelgium/` (everything except `public`).
- Upload the contents of `public/` into `/home/USER/domains/YOURDOMAIN/public_html/` (including `.htaccess`, `build/`, `fonts/`, `images/`).
- Edit `public_html/index.php` so the two paths point at the app folder:
  ```php
  require __DIR__.'/../../../gobelgium/vendor/autoload.php';
  $app = require_once __DIR__.'/../../../gobelgium/bootstrap/app.php';
  ```
  (adjust the number of `../` to match your layout) and the `storage/framework/maintenance.php` line the same way.
- Storage link: with SSH run `php artisan storage:link`. Without SSH, create a symlink with a one-off PHP script, or set `FILESYSTEM_DISK=public` and copy `storage/app/public` to `public_html/storage` after uploads (Admin uploads need the symlink, so SSH is strongly recommended).

## 4. Production `.env`
Create `/home/USER/gobelgium/.env` (never commit it):
```
APP_NAME="Go Belgium Tours"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=            # php artisan key:generate --show
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=file
MAIL_MAILER=smtp               # Hostinger email SMTP: host smtp.hostinger.com, port 465/587
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=enquiries@yourdomain.com
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=enquiries@yourdomain.com
RECAPTCHA_SITE_KEY=...         # real keys from google.com/recaptcha (v2 checkbox)
RECAPTCHA_SECRET_KEY=...
GHL_API_TOKEN=                 # Private Integration Token
GHL_LOCATION_ID=
ADMIN_SEED_EMAIL=you@yourdomain.com
ADMIN_SEED_PASSWORD=a-long-unique-password
```
Add the domain to the reCAPTCHA key's allowed domains.

## 5. First run (SSH)
```
php artisan migrate --force
php artisan db:seed --force          # first deploy only (creates admin + starter content)
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan ghl:setup                # once the GHL keys are in .env
```
Without SSH: import the database from a local `mysqldump` through phpMyAdmin and upload a pre-built `bootstrap/cache` (run the three cache commands locally with production values only if you can; otherwise skip them).

After every deploy: `php artisan migrate --force` and the three cache commands (or `php artisan optimize`).

## 6. Queue worker and scheduler (cron)
Shared hosting cannot keep a daemon alive, so run the queue from cron. hPanel > Advanced > Cron Jobs, every minute:
```
* * * * * /usr/bin/php /home/USER/gobelgium/artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
* * * * * /usr/bin/php /home/USER/gobelgium/artisan schedule:run >> /dev/null 2>&1
```
This sends enquiry emails and GHL syncs within a minute. (On a VPS, use Supervisor with `php artisan queue:work --tries=4`.)

## 7. Sitemap and search
`/sitemap.xml` and `/robots.txt` are generated automatically from the database. After launch submit the sitemap in Google Search Console and run the SEO_CHECKLIST.md.

## 8. Security checks before launch
- `APP_DEBUG=false`; `.env` not reachable over the web.
- Change the seeded admin password; remove unused admins.
- Real reCAPTCHA keys; GTM ID set; notification emails set.
- Legal pages reviewed by a professional; all `[CONFIRM: ...]` items resolved (CONTENT_TO_CONFIRM.md).
- Back up the database regularly (hPanel Backups plus your own export).
