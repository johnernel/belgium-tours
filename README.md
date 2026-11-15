# Go Belgium Tours

Landing-page site for Google Ads (Laravel 12, Blade, Tailwind v4, Alpine). Full notes for developers are in [CLAUDE.md](CLAUDE.md); guides are in [docs/reference](docs/reference).

## Get it running (XAMPP, three commands)

1. Start Apache and MySQL in XAMPP, create the database (`CREATE DATABASE gobelgium CHARACTER SET utf8mb4;`) and copy `.env.example` to `.env`.
2. `composer install && npm install && php artisan key:generate`
3. `php artisan migrate --seed && php artisan storage:link && npm run build && php artisan serve`

Open http://127.0.0.1:8000. Admin: http://127.0.0.1:8000/admin (email and password from `ADMIN_SEED_EMAIL` / `ADMIN_SEED_PASSWORD` in `.env`).

Run the tests with `php artisan optimize:clear && php artisan test`.
