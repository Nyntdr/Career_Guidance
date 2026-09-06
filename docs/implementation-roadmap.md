# CareerGuide AI — Implementation Roadmap (Current Phase)

> **Status:** **A ✅**, **B ✅**, **C ✅**, **D ✅**, **E ✅** and **G ✅** (admin settings / dynamic config; Inter font self-hosted) are complete. Remaining: **F** (career pathways). Everything uses existing seeded data (8 career roles, 30+ skills, 18 learning resources, 12 job postings) and light deterministic logic only.
>
> **Deferred (future phase):** datasets (O\*NET / ESCO / job-posting / resume corpora) + importers, and advanced algorithms (TF-IDF / cosine / embeddings, semantic matching, LLM AI-counselor). See [Deferred](#deferred--future-phase) at the bottom.

---

## Scope: completing feature areas (no AI, no new datasets)

### A. Candidate-facing content pages — ✅ COMPLETED

Read-only browsing pages for **Careers / Skills / Learning**, reachable from the user navbar.

| Feature | Schema used |
|---|---|
| Careers index + detail | `career_roles` (title, description, typical_seniority, avg_salary_range, growth_outlook) + `career_role_skills` (importance, min_proficiency) |
| Skills index (grouped by category) | `skills` (name, category_id) + `skill_categories` (name, slug) |
| Learning index (+ skills each covers) | `learning_resources` (title, provider, url, resource_type, duration_hours, cost) + `learning_resource_skills` |

- **Shipped:** `CareersController`, `SkillsController`, `LearningController`; `Pages/Careers/Index.vue`, `Pages/Careers/Show.vue`, `Pages/Skills/Index.vue`, `Pages/Learning/Index.vue`; real nav links.
- **Notes:** career-role search is server-side; skills uses client-side search + category filter; learning has server-side search + type filter.
- **Also shipped:** `LearningResourceSeeder` (18 resources across all 6 types).

### B. Personal job recommendations — ✅ COMPLETED

Score `job_postings` against the candidate's `user_skills`.

| Schema used |
|---|
| `job_postings` (title, company, location, seniority_level, raw_description) |
| `job_required_skills` (job_posting_id, skill_id, importance, min_proficiency) |
| `user_skills` (profile_id, skill_id, proficiency_level) |

- Reuses the **existing essential/preferred weighting** already in `DashboardController::computeMatches` (essential 70% / preferred 30%, proficiency gate). Job importance values are `required` (→ 70% weight) and `preferred` (→ 30% weight).
- **Shipped:** `JobsController` (+ `scoreJobs()`), `Pages/Jobs/Index.vue` (stats row, match % badges, matched vs. missing skill chips), route `jobs.index`, navbar **Jobs** link, `JobPostingSeeder` (12 postings across the seeded domains).
- **No dependencies.**

### C. Persisted skill-gap assessment → learning → roadmap — ✅ COMPLETED

Today `computeGaps` runs in-memory only. Formalize a **"Run Assessment"** flow that persists results and tracks progress.

| Schema used |
|---|
| `gap_assessments` (profile_id, target_role_id, overall_readiness_score) |
| `gap_assessment_items` (gap_assessment_id, skill_id, required_level, current_level, gap_size, priority) |
| `gap_recommendations` (gap_assessment_item_id, learning_resource_id, rank) — ranks resources whose skills cover the gap skill |
| `progress_tracking` (profile_id, gap_assessment_item_id, status) — `not_started → in_progress → completed`, unique (profile_id, gap_assessment_item_id) |

- **Shipped:** `app/Services/SkillGapAnalyzer.php` (`run()` + `rankRecommendations()` + `readinessScore()`); `GapAssessmentController@run` (`POST /assessment/run`, defaults to best-matched role); `RoadmapController` (`GET /roadmap`, `PATCH /roadmap/progress/{progress}`); `Pages/Roadmap/Index.vue` (readiness stats, gap list with status segmented control, recommended-resource cards, empty-state role picker); navbar **Roadmap** link + dashboard "View roadmap" link.
- **Notes:** every run keeps history; latest assessment feeds the dashboard and roadmap. Resources ranked by how many other gap skills in the same assessment they also cover (then cost asc), top 3 per gap. Items with no covering resource still get a progress row.
- **No dependencies.**

### D. Resume upload + parsing (deterministic)

Upload a resume, extract text, segment into sections, and extract skills via dictionary match.

| Schema used |
|---|
| `resumes` (profile_id, file_url, file_type, raw_text, parsed_json, version, is_primary, uploaded_at) |
| `resume_sections` (resume_id, section_type, content, order_index) |
| `skills` + `skill_aliases` (skill_id, alias) — dictionary match for skill extraction |
| `user_skills` — upsert with `source = 'resume_extracted'` |

- **Open question:** accepted file types — plain `.txt` / pasted text only (**no new dependency**) vs. also PDF/Word (**adds a parser package**).
- **✅ COMPLETED (decision: `.txt` + pasted text only; PDF/Word kept as a future area):**
  - **Shipped:** `app/Services/ResumeParser.php` (segment text → `resume_sections`; dictionary-match skills + `skill_aliases` with word boundaries → `user_skills` upserted with `source = 'resume_extracted'`); version increments per profile; first resume auto-fires `is_primary`; parsed sections/skills stored in `parsed_json`. `ResumeController` (`GET/POST /resumes`, `PATCH /resumes/{resume}/primary`, `DELETE /resumes/{resume}`, `GET /resumes/{resume}/download`); `Pages/Resumes/Index.vue` (upload/paste form, version cards, section + skill breakdown, per-resume ATS scan selector); navbar **Resumes** link. `SkillAliasSeeder` added (ML, JS, K8s, REST, etc.).
  - **Notes:** `.txt` files stored on the `local` disk (mounted volume) under `resumes/`; `resumes.file_url` is NOT NULL so pasted-text resumes get an empty (non-null) string.

### E. ATS scan (deterministic scoring)

Pick a `job_posting` + the user's primary resume and compute + persist a scan.

| Schema used |
|---|
| `ats_scans` (resume_id, job_posting_id, overall_score, keyword_match_score, structure_score, formatting_score, readability_score) |
| `ats_matched_keywords` (ats_scan_id, keyword, matched, frequency_in_resume, frequency_in_jd) |
| `ats_scan_details` (ats_scan_id, category, detail, severity, suggestion) |

- **keyword_match_score** — required-skill overlap + term frequency of JD requirements in resume text.
- **structure_score** — section presence / completeness.
- **formatting_score** — heuristics: bullet usage, action verbs, quantified points.
- **readability_score** — **Flesch-Kincaid** grade level (pure math).
- **overall_score** — weighted blend of the above.
- **Deliverable:** scan service + results page with breakdown and suggestions.
- **No dependencies.**
- **✅ COMPLETED:**
  - **Shipped:** `app/Services/Readability.php` (pure Flesch-Kincaid grade → 0–100 rescaled score); `app/Services/AtsScanner.php` (`scan()` persists `ats_scans` + `ats_matched_keywords` + `ats_scan_details`; keyword score = required 70% / preferred 30% of JD-skill overlap with alias-aware term frequency; structure = expected sections present; formatting = bullets/action-verbs/quantified points; readability = Flesch-Kincaid; overall = 50/15/15/20 weighted). `AtsScanController@show` (`GET /ats-scans/{scan}`, ownership check); `Pages/AtsScans/Show.vue` (overall gauge, 4 component bars, per-keyword Found/Missing + frequencies, required vs preferred skill chips, severity-coded suggestions).
  - **Notes:** verified end-to-end in Docker — sample Data Scientist resume parsed into 5 sections / 8 skills, scan vs Senior Data Scientist = overall 73 (keyword 68, structure 100, formatting 63, readability 73) with correct missing-keyword details (Statistics, Deep Learning).

### F. Career pathways

Seed a hand-written set of `career_paths` edges and display progression (no graph algorithm).

| Schema used |
|---|
| `career_paths` (from_role_id, to_role_id, typical_years, notes) |

- **Deliverable:** ~6–10 seeded edges; show "Pathways from this role" on the Careers detail page.

### G. Admin settings (dynamic config) — ✅ COMPLETED

Generic key-value settings so branding/scoring/limits are editable at runtime.

- **Shipped:** `settings` table (uuid + `key` unique + `value` json); `app/Models/Setting.php`; `config/settings.php` (single source of truth: each key defines label/type/group/default — adding a setting is one config entry, no migration); `app/Support/Settings.php` helper (per-request cache, DB override falls back to config default, `save()` writes overrides, null reverts to default); `Admin/SettingsController` (`GET/PUT /admin/settings`); `Pages/Admin/Settings/Index.vue` (grouped form: Branding / Currency / Scoring / Limits, live weight-total check); admin navbar **Settings** link; settings shared app-wide via `HandleInertiaRequests` (`site_name`, `currency_symbol`, `currency_position`).
- **Consumed by:** brand text (navbar / guest / welcome — now dynamic); money formatting (Careers Index/Show, Learning, Roadmap, Dashboard `formatSalary`, admin labels) via `resources/js/lib/format.js` `formatMoney()` + dynamic currency; `AtsScanner` scoring weights; `top_matches` (Jobs + Dashboard match/gap limits); `admin_page_size` (all admin list pagination).
- **Settings:** `site_name` (Ujjwol Bato), `currency_symbol` ($), `currency_position` (before), `ats_weights` (50/15/15/20), `top_matches` (10), `admin_page_size` (20).
- **No dependencies.** Defaults are config-only; admin saves create DB overrides.

---

## New infrastructure introduced

- **`app/Services/`** — service classes:
  - `SkillGapAnalyzer` — ✅ shipped (Phase C), with `run()`, `rankRecommendations()`, `readinessScore()`
  - `ResumeParser` — ✅ shipped (Phase D)
  - `AtsScanner` — ✅ shipped (Phase E)
  - `Readability` — ✅ shipped (Phase E)
- **Storage disk config** for resume uploads (`storage` is already a mounted volume in Docker; .txt files stored under `resumes/`).
- **New candidate routes/controllers/pages**; navbar link updates.
- **Seeder additions:** `LearningResourceSeeder` (A), `JobPostingSeeder` (B), `SkillAliasSeeder` (D), `career_paths` (area F).
- **Gotcha (learned in A):** many-to-many links like `learning_resource_skills` have a UUID `id` PK and no model behind them, so plain `sync()` fails. Wire a `Pivot` model + `HasUuids` into the relation (`using(...)`) before syncing pivot tables.

---

## Build order

1. **A** — content pages (Careers / Skills / Learning) — ✅ done
2. **B** — recommended jobs — ✅ done
3. **C** — gap assessment + roadmap — ✅ done
4. **D + E** — resume upload/parse + ATS scan (ATS needs resume text) — ✅ done
5. **F** — career pathways — next
6. **G** — admin settings (dynamic branding / currency / scoring / limits) + Inter font — ✅ done

---

## Verification

- `php artisan migrate` — for any new migrations (tables already exist for most areas).
- `npm run build` — rebuild frontend after new pages.
- Manual flows: browse pages → run assessment → upload resume → scan against a job → mark roadmap items.
- Feature tests for the new endpoints.
- **Host-artisan reminder:** local `php artisan ...` needs `.env` → `DB_HOST=127.0.0.1` / `DB_PORT=3307` (see README "Command reference — Docker vs Local"), or run via `docker compose exec app php artisan ...`.

---

## Deferred (future phase)

- **Datasets + importers:** O\*NET / ESCO occupation→skill taxonomies, job-posting corpora, labeled resume corpora. Populate `skills` (incl. `taxonomy_source`, `embedding`), `career_roles`, `career_role_skills`, `job_postings`, `resumes`.
- **Algorithms:** TF-IDF + cosine similarity, embedding-based semantic matching (real vectors in the `embedding` JSON columns), career-path graph traversal (BFS / shortest path).
- **LLM / AI-counselor tier:** structured resume parsing, generated rationale for recommendations, AI chat assistant acting as the counselor role.

---

## Open questions

- **Resume file types (D):** `.txt` / pasted-text only (no dependency) vs. also PDF/Word (adds a parser package).
- **Navbar extras (optional):** wire the placeholder Search icon to a results page; notification bell stays decorative for now.