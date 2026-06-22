# Deployment guide (free tier)

Publish **Urmaza Dental** using a **$0/month** stack:

| Layer | Service | Role |
|-------|---------|------|
| **Frontend** | [Vercel](https://vercel.com) (Hobby) | Nuxt static SPA |
| **API** | [Render](https://render.com) (Free) | Laravel 12 |
| **Database** | [Neon](https://neon.tech) (Free) | PostgreSQL |

**Trade-offs on free tiers**

- Render **sleeps** after ~15 minutes idle → first request can take 30–60 seconds.
- Neon free DB has storage limits (enough for a small clinic).
- **Uploaded appointment images** on Render free disk are **not persistent** across redeploys. For production photos, add S3/Cloudinary later.

---

## Architecture

```
Browser
   │
   ├─► https://your-app.vercel.app          (Nuxt SPA)
   │         NUXT_PUBLIC_API_BASE ───────────────┐
   │                                              │
   └─► https://urmaza-api.onrender.com/api/*  ◄──┘ (Laravel)
              │
              └─► Neon PostgreSQL (DB_URL)
```

CORS is already permissive in `backend/config/cors.php` for cross-origin API calls from Vercel.

---

## 1. Database — Neon (PostgreSQL)

1. Sign up at [neon.tech](https://neon.tech).
2. Create a project (e.g. `urmaza-dental`).
3. Open **Dashboard → Connection details** and copy the **connection string** (URI), e.g.  
   `postgresql://user:pass@ep-xxx.ap-southeast-1.aws.neon.tech/neondb?sslmode=require`
4. Keep this for Render env vars as **`DB_URL`**.

Laravel uses `DB_CONNECTION=pgsql` and reads `DB_URL` from `config/database.php`.

---

## 2. API — Render (Docker)

### Option A — Blueprint (recommended)

1. Push this repo to **GitHub**.
2. In [Render Dashboard](https://dashboard.render.com) → **New → Blueprint**.
3. Connect the repo; Render reads root **`render.yaml`**.
4. Set **secret** environment variables when prompted:
   - **`APP_KEY`** — generate locally:  
     `cd backend && php artisan key:generate --show`
   - **`APP_URL`** — your Render URL, e.g. `https://urmaza-api.onrender.com`
   - **`DB_URL`** — Neon connection string from step 1
5. Deploy. Wait for build + first migrate (runs in container start command).

### Option B — Manual web service

1. **New → Web Service** → connect repo.
2. **Root directory:** `backend`
3. **Runtime:** Docker
4. **Dockerfile path:** `Dockerfile`
5. **Plan:** Free
6. **Health check path:** `/up`
7. Add environment variables (see [Production env vars](#production-env-vars-backend-render)).

### After first deploy

1. Open **Render Shell** for the service (or use a one-off job) and run:
   ```bash
   php artisan db:seed
   ```
2. Change the default admin password (see `database/seeders/AdminUserSeeder.php`).
3. Hit `https://YOUR-SERVICE.onrender.com/up` — should return OK.

---

## 3. Frontend — Vercel

1. Sign up at [vercel.com](https://vercel.com) and **Import** the GitHub repo.
2. **Root Directory:** `frontend` (important).
3. **Framework Preset:** Other (or Nuxt if detected).
4. Build settings (also in `frontend/vercel.json`):
   - **Build command:** `npm run generate`
   - **Output directory:** `.output/public`
5. **Environment variables** (Production):

   | Name | Example |
   |------|---------|
   | `NUXT_PUBLIC_API_BASE` | `https://urmaza-api.onrender.com` (no trailing slash) |
   | `NUXT_PUBLIC_CLINIC_TIMEZONE` | `Asia/Manila` |
   | `NUXT_PUBLIC_FACEBOOK_URL` | Your Facebook page URL |

   See `frontend/.env.production.example`.

6. Deploy. Vercel rewrites all routes to `index.html` for SPA `/admin/*` navigation.

7. Open your Vercel URL → public site. Use header login → admin panel.

---

## Production env vars (backend / Render)

| Variable | Value |
|----------|--------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | From `php artisan key:generate --show` |
| `APP_URL` | `https://YOUR-SERVICE.onrender.com` |
| `APP_TIMEZONE` | `Asia/Manila` |
| `LOG_CHANNEL` | `stderr` |
| `DB_CONNECTION` | `pgsql` |
| `DB_URL` | Neon connection string |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `FILESYSTEM_DISK` | `public` |

---

## Local production smoke test

Test PostgreSQL locally before deploying:

```bash
cd backend
# In .env: DB_CONNECTION=pgsql and DB_URL=your-neon-string (or a local Postgres)
php artisan migrate:fresh --seed
php artisan serve
```

```bash
cd frontend
# .env: NUXT_PUBLIC_API_BASE=http://127.0.0.1:8000
npm run generate
npx serve .output/public
```

---

## Custom domain (optional)

- **Vercel:** Project → Settings → Domains → add `www.yourclinic.com`.
- **Render:** Service → Settings → Custom Domain → add `api.yourclinic.com`.
- Update **`APP_URL`** and **`NUXT_PUBLIC_API_BASE`** to match, then redeploy both.

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| API 500 on Render | Check **Logs**; usually missing `APP_KEY` or invalid `DB_URL`. |
| CORS / network errors from Vercel | Confirm `NUXT_PUBLIC_API_BASE` matches Render URL exactly (https, no trailing slash). Rebuild Vercel after changing. |
| Migrations failed | Ensure Neon project is active; `DB_URL` includes `sslmode=require`. |
| Slow first load | Render free tier cold start — normal. Upgrade or use a paid plan for always-on. |
| Images missing after redeploy | Expected on free Render disk — use object storage for uploads in production. |
| `/admin` 404 on refresh | Vercel rewrites in `vercel.json` should handle this; redeploy frontend. |

---

## Files added for deployment

| File | Purpose |
|------|---------|
| `render.yaml` | Render Blueprint for Laravel API |
| `backend/Dockerfile` | PHP 8.2 + pgsql, migrate on start |
| `backend/.dockerignore` | Smaller Docker build |
| `frontend/vercel.json` | Static build + SPA rewrites |
| `frontend/.env.production.example` | Vercel env template |

---

## Paid upgrade path

When the clinic outgrows free tiers:

- **Render Starter** — no sleep, persistent disk for uploads.
- **Neon paid** — more storage and branches.
- **Single VPS** — Nginx + same domain for SPA and `/api` (see root `README.md`).
