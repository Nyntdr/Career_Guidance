# CareerGuide AI

An AI-based career guidance system built with **Laravel** (backend) and **Vue.js** (frontend via Inertia.js).

## Tech Stack

- **Backend:** Laravel 13, Laravel Breeze (authentication)
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS, Vite
- **Database:** MySQL 8.4 (via Docker)
- **Web Server:** Nginx (Alpine)
- **Containerization:** Docker Compose (multi-stage build: Composer → Node/Vite → PHP-FPM → Nginx)

## Pages

| Page  | Route    | Description                          |
|-------|----------|---------------------------------------|
| Index | `/`      | Landing page with career guidance overview |
| Login | `/login` | User authentication page              |

## Getting Started (Docker — recommended)

### Prerequisites

- Docker & Docker Compose

### Setup

1. **Environment variables**

   Create a `.env` file in the project root (used only by Docker Compose, not by Laravel directly — app config is passed via the `environment:` block in `docker-compose.yml`):

   ```env
   APP_PORT=8082
   ```

   Adjust `APP_PORT` if 8082 conflicts with another project on your machine.

2. **Generate an application key**

   ```bash
   docker run --rm php:8.4-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
   ```

   Copy the output and paste it into `docker-compose.yml` under the `app` service's `environment:` block as `APP_KEY`.

3. **Build and start the stack**

   ```bash
   docker compose build --no-cache
   docker compose up -d
   ```

   This starts three containers:
   - `mysql` — MySQL 8.4 database
   - `app` — PHP-FPM (Laravel)
   - `nginx` — web server, exposed on the host

4. **Run migrations** (if not already run automatically by the entrypoint)

   ```bash
   docker compose exec app php artisan migrate --force
   ```

5. **Visit the app**

   ```
   http://localhost:8082
   ```

   (or whichever port you set as `APP_PORT`)

### Useful commands

```bash
# View logs
docker compose logs app --tail=50
docker compose logs nginx --tail=50

# Run artisan commands
docker compose exec app php artisan migrate:status
docker compose exec app php artisan tinker

# Rebuild after Dockerfile/dependency changes
docker compose build --no-cache
docker compose up -d --force-recreate
```

### Command reference — Docker vs Local

> **Golden rule:** whenever a command starts with `php artisan ...`, it runs PHP inside the
> `app` container. From the host you must prefix it with `docker compose exec app`.
> Only `docker`, `docker compose`, `npm`, and the `mysql` client can run directly on the host.
>
> **Why:** app config is passed via the `environment:` block in `docker-compose.yml`, where
> `DB_HOST=mysql` — a hostname that only resolves *inside* the Docker network. Host PHP
> cannot resolve it, so running a bare `php artisan ...` from your terminal fails with
> `getaddrinfo for mysql failed`.

| Task | Inside Docker (✔ recommended) | Host / local (requires `.env`, ✔b) |
|---|---|---|
| Run migrations | `docker compose exec app php artisan migrate` | `php artisan migrate` ✔b |
| Seed the database | `docker compose exec app php artisan db:seed` | `php artisan db:seed` ✔b |
| Fresh migrate + seed | `docker compose exec app php artisan migrate:fresh --seed` | `php artisan migrate:fresh --seed` ✔b |
| Interactive tinker | `docker compose exec app php artisan tinker` | `php artisan tinker` ✔b |
| List routes | `docker compose exec app php artisan route:list` | `php artisan route:list` ✔b |
| Check migration status | `docker compose exec app php artisan migrate:status` | `php artisan migrate:status` ✔b |
| Build frontend assets | `docker compose exec app npm run build` | `npm run build` |
| Run tests | `docker compose exec app composer test` | `composer test` ✔b |
| MySQL shell | `docker compose exec mysql mysql -u laravel -psecret career_guidance` | `mysql -h 127.0.0.1 -P 3307 -u laravel -psecret career_guidance` |

**✔b (local commands only work if your `.env` points at the Docker MySQL):**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=career_guidance
DB_USERNAME=laravel
DB_PASSWORD=secret
```

- Docker's MySQL is exposed on the host at port **3307** (mapped from the container's 3306) to
  avoid clashing with a locally installed MySQL. The local MySQL on port 3306 **rejects** the
  app credentials — don't point `.env` there.
- These values live in **`.env`** (gitignored). They do **not** affect the containers; those use
  docker-compose's `environment:` block.

> **Troubleshooting:** if you see `php_network_getaddresses: getaddrinfo for mysql failed`
> or `Connection refused`, you ran a host `php artisan ...` command while `.env` still says
> `DB_HOST=mysql`. Either fix `.env` as above or use `docker compose exec app ...`.

### Checking the database

**Via the MySQL container:**

```bash
docker compose exec mysql mysql -u laravel -psecret career_guidance
```

**From the host** (mapped to port `3307`):

```bash
mysql -h 127.0.0.1 -P 3307 -u laravel -psecret career_guidance
```

**Via a GUI client** (TablePlus, DBeaver, HeidiSQL, etc.):

| Setting  | Value            |
|----------|------------------|
| Host     | `127.0.0.1`      |
| Port     | `3307`           |
| Username | `laravel`        |
| Password | `secret`         |
| Database | `career_guidance`|

### Demo user

```bash
docker compose exec app php artisan tinker
>>> \App\Models\User::factory()->create(['email' => 'demo@careerguide.ai', 'password' => bcrypt('password')]);
```

Or register via `/register`.

---

## Getting Started (Local, without Docker)

### Prerequisites

- PHP 8.4+
- Composer
- Node.js 18+

### Installation

```bash
cd career_guidance
composer install
npm install
cp .env.example .env   # if .env doesn't exist
php artisan key:generate
php artisan migrate
npm run build
```

> **Note for local commands:** if your database is the one running in Docker (exposed on host
> port `3307`), point `.env` at `DB_HOST=127.0.0.1` and `DB_PORT=3307`. Otherwise local
> `php artisan ...` commands will fail with `getaddrinfo for mysql failed`. See
> [Command reference — Docker vs Local](#command-reference--docker-vs-local).

### Run the development server

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite (hot reload)
npm run dev
```

Visit **http://localhost:8000** for the index page and **http://localhost:8000/login** for the login page.

### Demo user

```bash
php artisan tinker
>>> \App\Models\User::factory()->create(['email' => 'demo@careerguide.ai', 'password' => bcrypt('password')]);
```

Or register via `/register`.

## Project Structure

```
career_guidance/
├── app/                    # Laravel backend (controllers, models)
├── docker/
│   ├── nginx/              # Nginx config (default.conf)
│   └── php/                # PHP config + container entrypoint
├── resources/
│   ├── js/
│   │   ├── Pages/
│   │   │   ├── Welcome.vue     # Index / landing page
│   │   │   └── Auth/Login.vue  # Login page
│   │   ├── Layouts/              # Page layouts
│   │   └── Components/           # Reusable Vue components
│   └── css/app.css
├── routes/
│   ├── web.php             # Web routes
│   └── auth.php            # Auth routes (login, register, etc.)
├── database/               # Migrations & seeders
├── Dockerfile              # Multi-stage build: vendor → frontend → app → web
└── docker-compose.yml      # app, nginx, mysql services
```

## Notes

- The Docker build uses PHP **8.4** (required by current Composer dependencies).
- The `app` service runs PHP-FPM only — it has no host-exposed port. All requests go through `nginx`, which proxies to `app` on the internal Docker network.
- MySQL is exposed on host port **3307** (mapped from container port 3306) to avoid clashing with a locally installed MySQL instance on the default port 3306.

## License

MIT
