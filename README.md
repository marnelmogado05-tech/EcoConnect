# EConnect

EConnect is a Laravel-based incident reporting and notification platform with Firebase push notification support, Excel exports, PWA capabilities, and a modern Tailwind/Vite frontend.

## Key Features

- Incident management and status tracking
- Firebase Cloud Messaging (FCM) push notifications
- User authentication and profile management
- Event-driven notification dispatch via queues
- Excel export support with `maatwebsite/excel`
- Progressive Web App (PWA) support
- Alpine.js UI enhancements and SweetAlert2 alerts

## Tech Stack

- PHP 8.2
- Laravel 12
- MySQL / SQLite / supported Laravel database
- Tailwind CSS
- Vite
- Alpine.js
- Firebase (via `kreait/laravel-firebase`)
- Excel export (`maatwebsite/excel`)
- PWA support (`erag/laravel-pwa`)

## Installation

1. Clone the repository:
   ```bash
   git clone <repo-url> EConnect
   cd EConnect
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install frontend dependencies:
   ```bash
   npm install
   ```

4. Copy environment files:
   ```bash
   cp .env.example .env
   ```

5. Generate the application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your `.env` file:
   - `APP_NAME`
   - `APP_URL`
   - database connection settings
   - `QUEUE_CONNECTION=database` (recommended for notifications)
   - `FIREBASE_CREDENTIALS=storage/app/firebase-auth.json`

7. Run migrations:
   ```bash
   php artisan migrate
   ```

8. Build frontend assets:
   ```bash
   npm run build
   ```

## Local Development

Start the app and frontend tooling:

```bash
php artisan serve
npm run dev
```

If you want a single command for local development, use the `dev` script in `composer.json`:

```bash
composer run dev
```

## Firebase Push Notification Setup

This project includes a Firebase push notification implementation using `kreait/laravel-firebase`.

### Required Setup

- Add your Firebase service account JSON file to `storage/app/firebase-auth.json`
- Set `FIREBASE_CREDENTIALS=storage/app/firebase-auth.json` in `.env`
- Start a queue worker:
  ```bash
  php artisan queue:work
  ```

### Notification Routes

The application provides API endpoints for notification token management:

- `POST /api/notifications/save-token`
- `POST /api/notifications/remove-token`
- `POST /api/notifications/remove-all`
- `GET /api/notifications/status`

### Test a Notification

```bash
php artisan notifications:test 1
php artisan queue:work
```

## Useful Commands

- `php artisan migrate` - run database migrations
- `php artisan migrate:fresh --seed` - reset database and seed
- `php artisan test` - execute automated tests
- `npm run dev` - run Vite in development mode
- `npm run build` - build frontend assets for production
- `php artisan queue:work` - process queued jobs

## Testing

The project uses Pest for test execution. Run:

```bash
php artisan test
```

## Configuration Notes

The main config files include:

- `config/app.php`
- `config/database.php`
- `config/firebase.php`
- `config/queue.php`
- `config/pwa.php`

## Project Structure

- `app/` — application code, controllers, models, services, events, listeners
- `routes/` — route definitions
- `resources/` — views, CSS, JavaScript
- `public/` — web entrypoint and assets
- `database/` — migrations, factories, seeders
- `tests/` — automated test suite

## Documentation

Reference project-specific documentation for implementation details:

- `FIREBASE_SETUP_KREAIT.md` — Firebase notification setup summary
- `NOTIFICATIONS_QUICK_START.md` — quick usage examples for push notifications
- `IMPLEMENTATION_SUMMARY.md` — implementation summary and feature overview
- `TODO.md` — planned improvements and next work items

## License

This project is open source and available under the MIT license.
