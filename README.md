# Integral Valet Management

Vue 3 frontend and Laravel 13 API for valet parking, buildings, staff, tickets, payments, subscriptions, and reporting.

## Requirements

- Node.js 24 and npm
- PHP 8.3+ (PHP 8.4+ for the included PHPUnit 12 tests), Composer 2
- PHP extensions: mbstring, openssl, PDO, pdo_sqlite, fileinfo, curl, DOM, XML

SQLite is sufficient for local development. No separate database server is required.

## Backend setup

From `backend`:

```sh
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

The example environment uses SQLite. Keep this terminal running.

## Frontend setup

In a second terminal, from `frontend`:

```sh
npm ci
npm run dev -- --host=127.0.0.1
```

Open http://127.0.0.1:5173. API calls default to `/api/v1` and the development server forwards them to port 8000. Override `VITE_API_URL` in a local frontend `.env` if needed.

The local seed account is `admin@btrvalet.com`, password `password`. Change this password before exposing the application to other users.

## Checks

```sh
# frontend
npm run build
# backend
php artisan test
```

The frontend uses Vite's native config loader with Node 24 to avoid Windows config bundling issues.

## Deployment

GitHub stores the source code; GitHub Pages cannot run the PHP backend. A live deployment needs a PHP-capable host, a persistent database, a fresh `APP_KEY`, `APP_DEBUG=false`, HTTPS, and a web server pointing at `backend/public`. Serve `frontend/dist` with SPA fallback and route `/api` to Laravel, or configure `VITE_API_URL` and allowed origins for separate hosting. Do not upload `.env`, local databases, or dependencies.
