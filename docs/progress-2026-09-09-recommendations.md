# Progress — 2026-09-09 (phase H): AI career recommendations

## Done

Implemented the full recommendation pipeline, deterministic by first release with a dormant (placeholder) LLM rationale tier.

### New services
- **`app/Services/RoleMatchScorer.php`** — extracts the overlap math (essential 70% / preferred 30%, proficiency gate) that was duplicated in `DashboardController::computeMatches`, `SkillGapAnalyzer::defaultTargetRole`, and `JobsController::scoreJobs`. Returns `score`, `matched`, `missing` (skill ids).
- **`app/Services/Ml/SkillSimilarity.php`** — pure-PHP keyword TF-IDF weighted cosine. `IDF = 1 + log(N/df)` (returns ≥1 per term so single-corpus scoring is still meaningful). This is the seam the `embedding` JSON columns will feed later (same cosine interface).
- **`app/Services/RecommendationEngine.php`** — scores every `career_role` with the admin-tunable blend (default `overlap·60 + semantic·40`), applies a hop-decayed career-path bonus (default 5, BFS ≤ 2 hops from strong direct matches), sorts, wipes + recreates top-`top_matches` `career_recommendations` rows. No-op when the profile has no skills.
- **`app/Services/RecommendationRationalizer.php`** — template rationale (matched/missing → outlook → education) always available. LLM path wired via the built-in HTTP client (OpenAI-compatible chat endpoint) but only fires when setting `llm_rationale=enabled` AND `RECOMMENDATION_LLM_KEY` is present; any failure silently falls back to the template.

### Wiring
- **`config/recommendations.php`** — weights (60/40), path bonus (5), LLM provider (`endpoint` = Gemini OpenAI-compat URL, `model` = `gemini-3.6-flash`, `timeout` 60, `max_tokens` 1024, `key` from `RECOMMENDATION_LLM_KEY`).
- **`.env.example`** — documents empty `RECOMMENDATION_LLM_ENDPOINT/KEY/MODEL` (+ optional `RECOMMENDATION_LLM_MAX_TOKENS` / `RECOMMENDATION_LLM_TIMEOUT`); **`docker-compose.yml`** passes them through to the app container.
- **Settings** — `recommendation_weights` (percent_group, Scoring), `recommendation_path_bonus` (number, Limits), `llm_rationale` (select, **ML**). Adding `ML` to the admin groups array also fixed the previously-unwired ML settings group (ml_enabled etc. now render). `UpdateSettingsRequest` now validates sum-to-100 for *both* percent groups (generalized the existing `ats_weights` check).
- **`RecommendationController`** — `GET /recommendations` (auto-generates when empty + has skills), `POST /recommendations/generate`. Routes registered in the auth group; navbar **Recommendations** link added.
- **Dashboard** — empty-fallback now calls `RecommendationEngine::generate()` (replaces the old `computeMatches` branch), dead method removed; career-matches card gained **Regenerate** (POST) + **View details** actions.
- **`Pages/Recommendations/Index.vue`** — stats row (skills assessed / recommendations / best match / last generated), ranked cards (score badge, category + salary, outlook, rationale box, matched vs growth-area chips, View role + Build roadmap links), Regenerate button, empty states.

### Tests (13 new; suite 43 → 56, assertions 153 → 205, all green)
- `tests/Unit/SkillSimilarityTest.php` — zero for empty role; full-similarity ceiling; ordering (core > related > unrelated role).
- `tests/Unit/RoleMatchScorerTest.php` — full overlap = 100; missing essential skill reported; below-min proficiency counts unmatched.
- `tests/Unit/RecommendationEngineTest.php` — generates + persists rationale; wipes & no-ops when no skills; regenerate replaces rows.
- `tests/Feature/RecommendationControllerTest.php` — auth guard; index renders generated recommendations + props; empty state; generate creates + flashes success.

## Gotchas hit
1. **TF-IDF zeroed out with a single corpus document:** the first smoothing `log((1+N)/(1+df))` collapses every vector to zero when `N = df = 1` → cosine always 0. Fixed with `idf = 1 + log(N/df)`, which is ≥ 1 for single-doc corpora.
2. **Service constructor dependency injection:** `RecommendationEngine` requires `RoleMatchScorer` + `SkillSimilarity` + `RecommendationRationalizer` — must be resolved via `app(RecommendationEngine::class)` (or the container), not `new`.
3. **No `CareerRoleFactory` / `ProfileFactory` exist** — tests use the real seeders and `$user->profile()->create([])`.

## Verified
- `docker compose exec -T app php vendor/bin/phpunit` → **OK (69 tests, 256 assertions)**.
- `npm run build` → OK (Recommendations page + admin settings compiled).
- `php artisan migrate:fresh --seed --force` → OK (8 roles, 11 career paths, 30 skills ...).
- Smoke test (admin user w/ Data Analyst skill set): 8 recommendations, top = Data Analyst @ **99.2** (`Your existing skills in SQL, Data Analysis, ... align well with this role.`). `llm_rationale=disabled`, key EMPTY → template path, zero provider calls.

## AI rationale — dynamic provider (Gemini ↔ Groq)

### Config
The active LLM provider is chosen at runtime from **Admin → Settings → ML → "AI rationale provider"** (select: `Gemini (Google)` or `Groq (GPT-OSS)`), stored in the `llm_provider` DB setting. Each provider block lives in `config/recommendations.php` under `providers`, driven entirely by env vars.

### Per-provider env vars
| Provider | Endpoint | Key | Model |
|---|---|---|---|
| **Gemini** (default) | `RECOMMENDATION_LLM_ENDPOINT` | `RECOMMENDATION_LLM_KEY` | `RECOMMENDATION_LLM_MODEL` (default: `gemini-3.6-flash`) |
| **Groq** | `RECOMMENDATION_LLM_GROQ_ENDPOINT` | `RECOMMENDATION_LLM_GROQ_KEY` | `RECOMMENDATION_LLM_GROQ_MODEL` (default: `openai/gpt-oss-120b`) |

`RECOMMENDATION_LLM_PROVIDER` sets the env-level default (`gemini`), but the Admin setting always wins at runtime.

### Gemini notes
- The 2026 key format is the new **`AQ.` Auth key** (AI Studio no longer issues `AIza`), authenticates fine on the OpenAI-compat route (verified live, 200 OK). `gemini-2.5-flash` returns **404 for new accounts** since mid-2026 — working model is **`gemini-3.6-flash`**.
- **Free-tier cap:** ~20 `generate_content_free_tier_requests` per rolling window (daily-ish). When exhausted, every call falls back to the template silently. 1-call-per-action design keeps usage minimal; a billed Google project removes the cap entirely.

### Groq notes
- Free tier: 30 RPM, **1000 output tokens per minute (OTPM)**, no card required. Default model is **`openai/gpt-oss-120b`** — non-reasoning, clean prose, `finish: stop`, verified live (~500 chars).
- **Why not Qwen:** all catalog Qwen models (`qwen/qwen3.6-27b`, `qwen/qwen3.8-27b`) run *mandatory reasoning* on this tier — the ` thinking ...  response` chain-of-thought consumed the entire 1024 output budget (and the 1000 OTPM cap) before any answer, yielding empty rationales. `qwen/qwen3-32b` doesn't exist (404 `model_not_found`). `thinking`/`enable_thinking` params are rejected ("unsupported"). So stick to `openai/gpt-oss-120b` (or `openai/gpt-oss-20b`, also fine).
- The strip logic in `RecommendationRationalizer::llm()` still removes `<think(?:ing)>...<\/think>` blocks (harmless insurance if a reasoning model is ever selected).
- Signup: console.groq.com → API Keys. Paste into `.env` as `RECOMMENDATION_LLM_GROQ_KEY=gsk_...`.
- Significantly larger daily budget than Gemini free tier — ideal for testing the "Explain with AI" button repeatedly.

### How it works (one call per action, provider-agnostic)
- `RecommendationEngine::generate()` writes **LLM rationale only for the top-ranked role**; every other role gets the deterministic template. Rationale source is persisted in `rationale_source` (`llm` or `template`).
- Cards whose `rationale_source` is not `llm` show **"Explain with AI"** → `POST /recommendations/{recommendation}/rationale` recomputes overlap for that role and makes **one call to whichever provider is selected** (429/503 auto-retried 3×), then updates the row. Regenerate costs 1 call, each button click costs 1 call.
- The chosen provider's key is mandatory — if the selected provider has no key set in `.env`, the template is used silently (no cross-provider fallback).
- `RecommendationRationalizer::llm()` retries 429/503 (3 attempts + short backoff) before falling back to the template; any other failure also falls back silently.

### Dockerfile / dev-deps reminders
- `vendor` build stage uses `--ignore-platform-req=ext-gd` (phpoffice/phpword runtime dep). Rebuild image after dependency changes.
- After container recreate: `docker compose exec app composer install --no-scripts` to restore dev deps (phpunit).

## Try it in the browser — Scenario A: new user sets up their profile
1. Open **http://localhost:8082** and sign in as `test@example.com` / `password` (or register).
2. Profile is auto-created on first dashboard visit. Add skills: **Profile → Skills** (e.g. Python, SQL, Statistics, Data Analysis).
3. Open **Recommendations** — first visit auto-generates 8 ranked roles with **1 LLM call** (only the top pick gets AI prose; the rest show the standard template + an **Explain with AI** button).
4. Click **Explain with AI** on a lower card → one call to the selected provider → that row's text is replaced with AI prose. If the text doesn't change on Gemini, the free-tier rate limit is busy — retry after the window resets or switch the provider dropdown to Groq.
5. **Regenerate** (top-right) re-runs the ranking — top pick re-gets AI prose.
6. Confirm the toggle: → **Admin → Settings → ML → "AI rationale (LLM)" = Enabled** and **"AI rationale provider"** = the one with a valid key (Gemini or Groq) (login `admin@example.com` / `password`).

## Try it in the browser — Scenario B: new user skips setup and uploads a resume
1. Register → **Skip** the onboarding.
2. **Resumes** → upload one of the repo fixtures: `tests/Fixtures/files/upload-sample.docx`, `.pdf`, or `.txt`.
3. Text is parsed into sections and skills (e.g. Python, SQL) are **auto-added to the profile** (`source: resume_extracted`) — even though the form was never filled.
4. Open **Dashboard / Recommendations** → same ranked cards; top pick has AI prose, others the button. The resume upload bootstrapped the AI flow with zero form entry.
5. Optionally **Scan** the resume against a seeded job (e.g. *Senior Data Scientist @ Nova Analytics*) → ATS results page. Error paths: `.exe` is rejected; an image-only PDF shows a graceful "no readable text" message.

## Next steps (provider ready)
1. ✔ **Done** — Groq key pasted into `.env` (`RECOMMENDATION_LLM_GROQ_KEY=gsk_...`); container recreated; verified live end-to-end.
2. In Admin → Settings → ML → set **AI rationale (LLM) = Enabled**, **AI rationale provider = Groq (GPT-OSS)** (already set in the seeded DB).
3. Regenerate recommendations or click "Explain with AI" on a card — one Groq/GPT-OSS call, template fallback on failure. Live check: top card `source=llm` with ~500-char prose.
4. To switch back to Gemini, just change the dropdown — no `.env` edit needed (Gemini key is already set). Note: switched models on Gemini today showed 429 free-tier exhaustion — Groq is the reliable one for this demo.