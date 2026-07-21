# AI Career Guidance System — Project Overview

## Architecture
- **Stack**: Laravel 12 + Inertia + Vue 3 (composition API) + Tailwind CSS
- **DB**: MySQL 8.4, 32 tables, UUID PKs on user-facing tables
- **Auth**: Laravel Breeze (Inertia/Vue stack)
- **Build**: Vite

---

## Feature Status

### 1. Database Schema & Migrations ✅
- 13 migrations create all 32 tables
- Tables: `personal_access_tokens`, `users`, `profiles`, `career_profiles`, `skills`, `career_roles`, `career_role_skill`, `user_skills`, `education`, `work_experiences`, `progress_tracking`
- Reference: `database/schema.md`

### 2. Eloquent Models ✅
- 25 models with relationships, UUID support, `$table` fix for singular-named migrations
- `User` model has `role` enum cast, `profile()` relation
- `ProgressTracking` model has `protected $table = 'progress_tracking'`

### 3. Validation Requests ✅
- 7 form request classes: `ProfileUpdateRequest`, `EducationRequest`, `WorkExperienceRequest`, `UserSkillRequest`, `CareerProfileRequest`, etc.

### 4. Controllers & API ✅
- `ProfileController` — edit/update/destroy + `updateCareer`
- `EducationController` — CRUD with `_onboarding` flag
- `WorkExperienceController` — CRUD with `_onboarding` flag
- `UserSkillController` — CRUD with `_onboarding` flag
- `DashboardController` — role-dispatched, real DB queries, skill overlap matching, gap computation, recent activity

### 5. Routes ✅
- All profile routes under `auth` middleware
- Onboarding skip route
- Role-based dashboard routes:
  - `/dashboard` → `DashboardController` (candidate)
  - `/admin/dashboard` → `Admin/DashboardController`
  - `/counselor/dashboard` → `Counselor/DashboardController`
- Role middleware: `role:admin`, `role:counselor`

### 6. Role-Based Access ✅
- `UserRole` backed enum: `Candidate`, `Counselor`, `Admin` with `label()` and `dashboardRoute()`
- `EnsureUserRole` middleware — checks role, redirects on mismatch
- `role` alias registered in `bootstrap/app.php`
- Admin dashboard: stats cards (users, candidates, counselors, admins, roles, skills)
- Counselor dashboard: candidate list with skills count
- Navbar in `AuthenticatedLayout.vue` is role-aware (different nav links per role)

### 7. Onboarding Wizard ✅
- 5-step wizard (career profile → skills → education → work → done)
- `HandleInertiaRequests::share` injects `onboarding` boolean
- Skip via session (`onboarding_skipped`)
- Controllers accept `_onboarding` flag to return wizard-friendly JSON

### 8. Vue Components & Pages ✅
- All Breeze components updated with `dark:` Tailwind variants
- `Profile/Edit.vue` — 7 sections (profile info, password, career, skills, education, work, delete)
- `Dashboard.vue` — onboarding modal trigger, real data sections, empty states
- `Admin/Dashboard.vue`, `Counselor/Dashboard.vue` — role-specific dashboards
- Vue partials for all profile forms

### 9. Dark Mode ✅
- `tailwind.config.js`: `darkMode: 'class'`
- `composables/useDarkMode.js` — localStorage + `prefers-color-scheme` detection
- `app.css` — CSS variables for dark mode
- Toggle in navbar (🌙/☀️)
- All components and layouts have `dark:` variants

### 10. Navbar Redesign ✅
- Logo + "CareerGuide" brand text
- Nav links: role-aware sets (candidate: Dashboard/Careers/Skills/Learning; admin: Dashboard; counselor: Dashboard/Candidates)
- Search icon (candidate only), dark mode toggle, notification bell with badge, user avatar initial + name dropdown
- Mobile hamburger includes all nav links + dark mode toggle

### 11. Seeders ✅
- `SkillSeeder` — 30 canonical skills across 8 categories (Programming, Data, AI/ML, Frontend, Backend, Cloud, DevOps, Tools, Process, Management, Soft Skills)
- `CareerRoleSeeder` — 8 roles (Data Scientist, ML Engineer, Software Engineer, Data Analyst, Product Manager, DevOps Engineer, Frontend Developer, Backend Developer) with 71 skill-requirement links
- Both seeders are idempotent (truncate + re-insert or use `firstOrCreate`)

---

## Key Files

| File | Purpose |
|---|---|
| `app/Enums/UserRole.php` | Role enum with label/dashboardRoute |
| `app/Http/Middleware/EnsureUserRole.php` | Role-check middleware |
| `bootstrap/app.php` | Middleware aliases |
| `app/Models/User.php` | User model with role cast |
| `app/Http/Controllers/DashboardController.php` | Role-dispatched dashboard |
| `app/Http/Controllers/Admin/DashboardController.php` | Admin stats |
| `app/Http/Controllers/Counselor/DashboardController.php` | Candidate listing |
| `resources/js/Layouts/AuthenticatedLayout.vue` | Role-aware navbar |
| `resources/js/composables/useDarkMode.js` | Dark mode logic |
| `resources/js/Pages/Dashboard.vue` | Candidate dashboard |
| `resources/js/Pages/Admin/Dashboard.vue` | Admin dashboard |
| `resources/js/Pages/Counselor/Dashboard.vue` | Counselor dashboard |
| `resources/js/Pages/Profile/Edit.vue` | Profile edit page |
| `database/seeders/SkillSeeder.php` | 30 skills |
| `database/seeders/CareerRoleSeeder.php` | 8 roles + skill pivots |
| `database/schema.md` | Full schema documentation |
