# CareerGuide AI — Implementation Roadmap (Current Phase)

> **Status:** current phase is completing the remaining feature areas using existing seeded data (8 career roles, 30+ skills) and light deterministic logic only.
>
> **Deferred (future phase):** datasets (O\*NET / ESCO / job-posting / resume corpora) + importers, and advanced algorithms (TF-IDF / cosine / embeddings, semantic matching, LLM AI-counselor). See [Deferred](#deferred--future-phase) at the bottom.

---

## Scope: completing feature areas (no AI, no new datasets)

### A. Candidate-facing content pages

The navbar already links **Careers / Skills / Learning**, but they all point to `/dashboard` — no dedicated pages exist yet.

| Feature | Schema used |
|---|---|
| Careers index + detail | `career_roles` (title, description, typical_seniority, avg_salary_range, growth_outlook) + `career_role_skills` (importance, min_proficiency) |
| Skills index (grouped by category) | `skills` (name, category_id) + `skill_categories` (name, slug) |
| Learning index (+ skills each covers) | `learning_resources` (title, provider, url, resource_type, duration_hours, cost) + `learning_resource_skills` |

- **Deliverables:** 3 read-only controllers + Vue pages; update nav links to real routes.
- **No dependencies.**

### B. Personal job recommendations

Score `job_postings` against the candidate's `user_skills`.

| Schema used |
|---|
| `job_postings` (title, company, location, seniority_level, raw_description) |
| `job_required_skills` (job_posting_id, skill_id, importance, min_proficiency) |
| `user_skills` (profile_id, skill_id, proficiency_level) |

- Reuses the **existing essential/preferred weighting** already in `DashboardController::computeMatches` (essential 70% / preferred 30%, proficiency gate).
- **Deliverable:** Recommended Jobs controller + page.
- **No dependencies.**

### C. Persisted skill-gap assessment → learning → roadmap

Today `computeGaps` runs in-memory only. Formalize a **"Run Assessment"** flow that persists results and tracks progress.

| Schema used |
|---|
| `gap_assessments` (profile_id, target_role_id, overall_readiness_score) |
| `gap_assessment_items` (gap_assessment_id, skill_id, required_level, current_level, gap_size, priority) |
| `gap_recommendations` (gap_assessment_item_id, learning_resource_id, rank) — ranks resources whose skills cover the gap skill |
| `progress_tracking` (profile_id, gap_assessment_item_id, status) — `not_started → in_progress → completed`, unique (profile_id, gap_assessment_item_id) |

- **Deliverables:** Run-Assessment flow + new **"Skill Development / Roadmap"** page (`resources/js/Pages/Dashboard/...` or dedicated page); candidate dashboard reads persisted data when present.
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

### F. Career pathways

Seed a hand-written set of `career_paths` edges and display progression (no graph algorithm).

| Schema used |
|---|
| `career_paths` (from_role_id, to_role_id, typical_years, notes) |

- **Deliverable:** ~6–10 seeded edges; show "Pathways from this role" on the Careers detail page.

---

## New infrastructure introduced

- **`app/Services/`** — first service classes (directory does not exist yet):
  - `ResumeParser`
  - `AtsScanner`
  - `SkillGapAnalyzer`
  - `Readability`
- **Storage disk config** for resume uploads (`storage` is already a mounted volume in Docker).
- **New candidate routes/controllers/pages**; navbar link updates.
- **Seeder additions** for `career_paths` (area F).

---

## Build order

1. **A** — content pages (Careers / Skills / Learning)
2. **B** — recommended jobs
3. **C** — gap assessment + roadmap
4. **D + E** — resume upload/parse + ATS scan (ATS needs resume text)
5. **F** — career pathways

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