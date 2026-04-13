# Appointment System

Monorepo for **Appointment System**: a public-facing website for booking and information, plus an **admin** area for appointments, patients, services, and audit logs. The stack is **Laravel 12** (API) and **Nuxt 4** (Vue 3 SPA with Vuetify).

## Repository layout

| Path        | Description |
|------------|-------------|
| `backend/` | Laravel API (`php artisan serve`, Sanctum auth, SQLite/MySQL) |
| `frontend/` | Nuxt 4 SPA (`npm run dev`), proxies `/api` and `/storage` to Laravel in development |

## Features (high level)

- **Public site** — Landing page, services (from API), highlights, contact, appointment request dialog.
- **Admin** — Dashboard, appointments (CRUD, statuses), patients, services (admin role), audit logs, in-app notifications for pending confirmations.
- **API** — Appointments (availability, analytics, calendar helpers), services, auth (`/login`, `/logout`, `/user`), audit logs for authenticated users.

## Requirements

- **PHP** 8.2+ and [Composer](https://getcomposer.org/)
- **Node.js** 20+ (or current LTS) and npm
- **Database** — Default example uses SQLite; MySQL/MariaDB works with `.env` changes

## Quick start (local)

### 1. Backend (Laravel)

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
# Optional: seed admin/assistant users and sample data
php artisan db:seed
php artisan storage:link
php artisan serve
```

API runs at **http://127.0.0.1:8000** by default.

**Seeded admin (development only):** after `db:seed`, check `database/seeders/AdminUserSeeder.php` for the default admin email and password — **change these before any production deploy.**

### 2. Frontend (Nuxt)

In a second terminal:

```bash
cd frontend
npm install
npm run dev
```

The SPA dev server (see terminal output, often **http://localhost:3000**) proxies **`/api`** and **`/storage`** to Laravel on port **8000**, so you usually do not need CORS setup locally.

### 3. Sign in to admin

Use the **logout** affordance on the public header to open login (per your `default` layout), or navigate to admin routes after authenticating. Admin routes live under `/admin/*`.

## Environment configuration

### Backend (`backend/.env`)

| Variable | Notes |
|----------|--------|
| `APP_URL` | Public URL of the Laravel app (e.g. `http://127.0.0.1:8000`) |
| `APP_TIMEZONE` | Clinic wall time (default `Asia/Manila` — keep in sync with frontend) |
| `DB_*` | Use SQLite (default in `.env.example`) or configure MySQL, etc. |

See `backend/.env.example` for the full list.

### Frontend (`frontend/.env` optional)

| Variable | Notes |
|----------|--------|
| `NUXT_PUBLIC_API_BASE` | API origin without trailing slash. Empty often means **same origin as the SPA** in production; in dev, the app resolves to `http://127.0.0.1:8000` when unset (see `app/utils/apiBase.js`). |
| `NUXT_PUBLIC_CLINIC_TIMEZONE` | Should match Laravel `APP_TIMEZONE` (default `Asia/Manila`). |
| `NUXT_PUBLIC_FACEBOOK_URL` | Clinic Facebook link on the public site. |

See `frontend/nuxt.config.ts` for `runtimeConfig.public` defaults.

## Production build

**Frontend**

```bash
cd frontend
npm run build
```

Serve the generated output per your host (Nuxt `ssr: false` — client-only SPA). Point `NUXT_PUBLIC_API_BASE` (or your reverse proxy) so `/api` reaches Laravel.

**Backend**

```bash
cd backend
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan migrate --force
```

Use a real web server (nginx, Apache, etc.), set `APP_DEBUG=false`, strong `APP_KEY`, and secure database credentials.

## Scripts reference

| Location | Command | Purpose |
|----------|---------|---------|
| `frontend/` | `npm run dev` | Nuxt dev server + API proxy |
| `frontend/` | `npm run build` | Production bundle |
| `backend/` | `php artisan serve` | Local API server |
| `backend/` | `php artisan migrate` | Run migrations |

## Tech stack

- **Backend:** Laravel 12, Laravel Sanctum (Bearer API auth), Eloquent, SQLite/MySQL
- **Frontend:** Nuxt 4, Vue 3, Vuetify (Nuxt module), Pinia, Sass, i18n (en / tl)

## License

Add your license here (e.g. MIT, proprietary) when you publish the repository.
