# CareerGuidance — Phase F: Career Pathways (Wed 09 Sep 2026)

> Scope: complete the last roadmap feature area (**F — career pathways**) — seed hand-written
> `career_paths` edges and display progression on the Careers detail page. Everything recorded
> here is important for the next phase (**AI career recommendations**, which consumes the same
> `career_paths` graph as a BFS scoring boost).

## 1. What was done

| Item | File | Detail |
|---|---|---|
| Seeder | `database/seeders/CareerPathSeeder.php` | 11 hand-written edges across the 8 seeded roles, each with `typical_years` + `notes`; resolves roles by title, `firstOrCreate` so re-runs are idempotent |
| Registration | `database/seeders/DatabaseSeeder.php` | `CareerPathSeeder::class` added after `CareerRoleSeeder` |
| Controller | `app/Http/Controllers/CareersController.php` | `show()` now loads `outgoingPaths.toRole` / `incomingPaths.fromRole` and passes `outgoingPaths` + `incomingPaths` (id, role title/id, typical_years, notes) to the view |
| UI | `resources/js/Pages/Careers/Show.vue` | Two new pathway cards ("Pathways from this role" + "Roles leading to this role", years badge + note per edge, links into other career detail pages); the old "Coming in the next phase" placeholder replaced, incl. a working "Run a skill-gap assessment for this role" action → `POST assessment.run` with `target_role_id` |
| Tests | `tests/Feature/CareerPathTest.php` | Seeder creates expected edges (count ≥ 10; Data Analyst → Data Scientist, Data Scientist → ML Engineer verified); seeder idempotent on re-run; careers.show passes both path sets via `inertiaProps` |
| Docs | `docs/implementation-roadmap.md` | Section F marked ✅; status line + build order updated |

### Seeded edges (11)

- Data Analyst → Data Scientist (2 yrs) · Data Analyst → Product Manager (3 yrs)
- Data Scientist → Machine Learning Engineer (2 yrs) · ML Engineer → Data Scientist (2 yrs)
- Software Engineer → DevOps Engineer (2 yrs) · Software Engineer → Machine Learning Engineer (3 yrs)
- Frontend Developer → Software Engineer (2 yrs) · Backend Developer → Software Engineer (2 yrs)
- Software Engineer → Backend Developer (1 yr) · Software Engineer → Frontend Developer (1 yr)
- Product Manager → Data Scientist (4 yrs)

## 2. Schema / infra notes

- **No migration needed.** `career_paths` table, `CareerPath` model, and
  `CareerRole::outgoingPaths()` / `incomingPaths()` relations already existed from the original
  design (`2026_07_20_000005_create_career_roles_table.php` creates the table; see also `database.dbml`).
- `CareerPath` uses `HasUuids` + `$guarded = []`, so seeded via `firstOrCreate(from_role_id, to_role_id)`.

## 3. Verification

- **Full suite:** `43 tests / 153 assertions OK` (was 40/125 — +3 tests / +28 assertions) in the
  container MySQL (`docker compose exec -T app php vendor/bin/phpunit`).
- **Frontend:** `npm run build` succeeds (prereq: the `v-else` after a `v-for`-inside-`<ul>` must
  sit on the element directly after a `<template v-if>` — a plain `v-else` after `</ul>` fails with
  "v-else/v-else-if has no adjacent v-if").
- **Fresh re-seed:** `migrate:fresh --seed --force` → 8 roles, **11 career_paths**.
- **HTTP smoke:** `/careers/{id}` returns 302 → `/login` unauthenticated (auth middleware, expected);
  the Inertia-props test covers the authenticated page contract.

## 4. Environment notes (recurring)

- Running PHPUnit against the container MySQL wipes the dev DB per run (`RefreshDatabase`), so the
  dev DB is left empty after a test pass. Restore with `migrate:fresh --seed --force`.
- New PHP/test files land in the container via bind mounts (`./app`, `./database`, `./tests`, …). Only
  `composer.json/lock` are baked (see `docker-compose.yml`). Vue changes need a host-side
  `npm run build` (the `vite` container only runs the dev server).

## 5. Next phase

**AI career recommendations (full hybrid).** Plan agreed: new `app/Services/RecommendationEngine.php`
(+ `RoleMatchScorer`, `Ml/SkillSimilarity`, `RecommendationRationalizer`) writing to the dormant
`career_recommendations` table; auto-generate on empty dashboard + "Regenerate" action; optional LLM
rationale with template fallback. **Career paths plug in here** as a BFS-based scoring boost
(1-hop reachable roles from the top overlap matches), per the roadmap's deferred "career-path graph
traversal (BFS / shortest path)".