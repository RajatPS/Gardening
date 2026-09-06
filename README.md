# VerdantOps

Laravel 12 foundation for a Plant Nursery and Garden Management Platform.

## Built From `projectplan.md`

- Plant and accessory storefront
- Gardening service booking
- Monthly plant maintenance subscriptions
- Smart reminder system
- AI assistant, plant encyclopedia, disease detection, and garden planner UI shells
- Customer dashboard
- Staff and admin operations surfaces
- Versioned REST API for the public product catalog and future Flutter clients

## Stack

- Laravel 12
- Blade
- Tailwind CSS 4
- Vite
- MySQL for production data

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run build
php artisan serve
```

For database-backed features, configure MySQL in `.env`, then run:

```bash
php artisan migrate --seed
```

The current local defaults use file sessions/cache and sync queues so the UI can run before the database is configured.

See [API.md](API.md) for the available REST endpoints and response contracts.
