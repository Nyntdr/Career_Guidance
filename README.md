# CareerGuide AI

An AI-based career guidance system built with **Laravel** (backend) and **Vue.js** (frontend via Inertia.js).

## Tech Stack

- **Backend:** Laravel 13, Laravel Breeze (authentication)
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS, Vite
- **Database:** SQLite (default, configurable)

## Pages

| Page | Route | Description |
|------|-------|-------------|
| Index | `/` | Landing page with career guidance overview |
| Login | `/login` | User authentication page |

## Getting Started

### Prerequisites

- PHP 8.2+
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

### Run the development server

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite (hot reload)
npm run dev
```

Visit **http://localhost:8000** for the index page and **http://localhost:8000/login** for the login page.

### Demo user

Create a test account:

```bash
php artisan tinker
>>> \App\Models\User::factory()->create(['email' => 'demo@careerguide.ai', 'password' => bcrypt('password')]);
```

Or register via `/register`.

## Project Structure

```
career_guidance/
├── app/                    # Laravel backend (controllers, models)
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
└── database/               # Migrations & seeders
```

## License

MIT
