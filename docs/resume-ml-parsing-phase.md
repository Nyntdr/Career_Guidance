# Resume Parsing — ML Tuning Phase (real-world corpus)

> Phase status: **completed** upload support, fixture corpus, test-env fix, ML tuning from
> real resumes, seeded/reproducible training, and `--from-fixtures` corpus mining; full
> suite green (94 tests, 391 assertions). **Pending:** corpus v3 enrichment, an
> out-of-distribution eval set, and the (optional) admin ML settings UI.

## Context

The app parses uploaded resumes into sections (`contact`, `summary`, `skills`, `experience`, `education`, …) with a **hybrid segmenter** (`app/Services/Ml/ResumeSegmenter.php`):

- **Deterministic tier:** exact dictionary heading match (`app/Services/ResumeParser.php` `HEADINGS`).
- **ML tier** (when trained models exist in `storage/app/ml`): per-line Rubix ML Random Forest label + K-Means smoothing, gated by a heading-shape check — extends detection to *fuzzy* heading variants.

Previous tuning (synthetic probes only): bigram/trigram phrase vocab, 500-tree forest, `default_confidence` floor, and a first-content-line guard.

## Goal of this phase

1. Support **PDF + DOCX** uploads (in addition to plain text).
2. Evaluate and tune the ML path against **~246 real resumes** the user provided.
3. Document what was done/completed and the remaining steps in this file.

---

## 1. PDF / DOCX / TXT upload support — COMPLETED

| Item | Detail |
|---|---|
| Dependencies | `smalot/pdfparser`, `phpoffice/phpword` (host + container synced) |
| Extractor | `app/Services/ResumeTextExtractor.php` — `txt`/`pdf`/`docx` extraction, `typeFor()`, descriptive errors for scanned PDFs and legacy `.doc` |
| Controller | `ResumeController::store` — `mimes:txt,pdf,docx`, `max:5120`, stores the section parse, deletes the stored file if extraction fails, records `file_type` |
| UI | `resources/js/Pages/Resumes/Index.vue` — `accept=".txt,.pdf,.docx"`, helper text, per-type file badge, download link for stored docs |
| Test data | LibreOffice-generated `tests/Fixtures/files/upload-sample.{docx,pdf,txt}` from known text |
| Tests | `tests/Feature/Resume/ResumeUploadTest.php` (4 tests / 17 assertions) |

## 2. Real-resume fixtures & diagnostic tool — COMPLETED

- Source: the user's `~/Downloads` — **224 DOCX** (real candidates), **2484 PDFs** across **24 job categories** (e.g. `INFORMATION-TECHNOLOGY`, `FINANCE`), and a large `Resume.csv`.
- Extracted 224 DOCX → `.txt` (LibreOffice, UTF-8) and one PDF per category via `pdftotext`.
- Committed **246 fixtures** to `tests/Fixtures/resumes/` (24 category PDFs + 222 DOCX). Fixtures contain real candidate names — committed with the user's approval.
- Added a temporary **`ml:diagnose`** command (`app/Console/Commands/MlDiagnose.php`) comparing dictionary vs ML segmentation across fixtures, printing per-ML-section anchor (switch) lines and RF heading-shaped proposals.

## 3. Root cause: broken test environment — COMPLETED

14 "pre-existing" Auth/Profile test failures were **not** caused by this feature:

- The container runs a stale baked-in `phpunit.xml`; docker-compose exports `APP_ENV=local` etc. into `$_SERVER`, which Laravel's `Env` resolves before `$_ENV` — so `runningUnitTests()` was false and **CSRF stripped → 419 on every POST**.
- Fixed by forcing test env values in `phpunit.xml` (`<env force>` + `<server force>`) and bind-mounting `./phpunit.xml:/var/www/html/phpunit.xml`.

Result: full suite green (38 tests → now 40).

## 4. ML tuning driven by real data — COMPLETED

### Findings from the corpus

- **RF alone cannot separate headings from content.** Real fuzzy headings score ~0.34–0.51, but short title-case content bullets reach ~0.41 (`Telephone Skills`, `Computer Skills`). Any single score threshold is a precision loser.
- **Consultant-style DOCX resumes alternate sections pathologically** (`experience,skills,experience,skills,…` — up to 16 sections) when content bullets (blank-line separated) get mislabeled.
- **Colon / case variants of dictionary headings** (`SUMMARY:`, `TECHNICAL SKILLS:`, `PROFESSIONAL EXPERIENCE:`) were a huge deterministic gap and were being guessed by RF (often wrongly, e.g. `Education:` → skills).
- `Responsibilities:` (and friends) inside each job block was the single biggest alternation driver (RF → skills).
- Date/company/attribution lines are ubiquitous and heading-shaped: `January 2006 to May 2007 Company Name`, `12/2015 to Current`, `Client: JPMorgan Chase`.

### Changes implemented

| Guard | Why |
|---|---|
| `default_confidence` 0.12 → **0.3** (`config/ml.php`, `config/settings.php`) | real fuzzy headings clear 0.3; bullets stay below |
| **Blank-line isolation** (`prevIsBlank`) | a heading must be preceded by a blank line (or be the first line) |
| **`looksLikeContent` structural negatives** | reject bullet glyphs/dashes, any 4-digit year (dates/ranges), `client/employer/current company/project name/role:` prefixes, and `Responsibilities:`/`duties`/`job|project description` subsection headers |
| **Canonical colon-stripped dictionary match** | `SUMMARY:`, `EDUCATION:`, `Professional Experience:` now resolve through the deterministic tier (correct labels, no RF) |
| **Dictionary-anchored ML scope** | ML splits allowed only inside the lead-in `contact` region when the doc has dictionary headings; document-wide only if the dictionary found **nothing** (`dict=[contact]`) |
| **Single-word headings** require all-caps or trailing colon | kills content lines like `Calendaring`, `Methodologies` |
| Retained | first-content-line buffer guard (names like `Jane Smith` stay in `contact`) |

### Measured impact (246 fixtures)

- Documents whose ML output differs from dictionary output: **197 → 148 → 93** across the tuning steps.
- 153 fixtures are now identical with ML on/off; the residual differences are almost all a single added `summary`/`skills` lead-in section the dictionary missed — no more section alternation (a few edge cases remain, e.g. `deepika_dc`).
- Regression suite green: 40 tests / 125 assertions.

## 5. Regression tests — COMPLETED

`tests/Feature/Ml/ExampleResumeTest.php` runs the full parser on real fixtures and is **auto-skipped when models are absent**:

- `ADVOCATE.txt` dictionary path is stable (contact first, contains experience/skills/education).
- With ML enabled, `ADVOCATE` gains `summary` without corrupting the dict structure into alternation.
- `docx_balaji_…_project_manager_txt.txt` (dictionary collapses to `[contact]`) is rescued to find `skills`.

---

## Next steps

1. **Corpus v3 enrichment (core, still open).** The shipped 149-line corpus is too small for
   13-way heading classification and measures **17.1%** holdout accuracy. Fixture mining
   (`--from-fixtures`) raises this to **32–36%**, but the weak labels it produces are
   *section content*, not heading candidates, and models beyond ~150 samples per label
   exhaust the 256 MB ceiling (see `docs/report-algorithms.md` §1.5). The open problem is
   therefore not "more lines" but "**true heading candidates**" — mine heading lines
   specifically (fuzzy heading positives such as `AREAS OF EXPERTISE`,
   `TECHNICAL SKILL-SET:`, `Work History`, and short title-case *content* negatives such as
   `Customer Service`, `Critical Thinking`, `Methodologies`) rather than section bodies.
2. **De-duplicate the heading-shape logic.** `ml:diagnose`'s global `isHeadingLikeStandalone()` should call the segmenter's gate so the tool can't drift from production; then keep or delete the command (it's phase tooling).
3. **Optional — admin ML settings UI.** The `ML` settings group (`ml_min_confidence`, `ml_cluster_purity`) is read at parse time but not exposed on the Admin → Settings page or in `UpdateSettingsRequest` rules; wiring it lets admins tune without config deploys. Same for the new drift reporting in `ml:status` (`ml_drift_window`, `ml_min_cluster_size`).
4. **Optional — eval script.** Track a miscategorization rate over the fixtures (`ml:diagnose --json`) to spot RF regressions on retrains. This matters more now that training is deterministic: the model changes only when the corpus or seed changes, so regressions will be reproducible rather than intermittent.
5. **Deploy/watch-outs.** Container `composer.json`/`composer.lock` are baked images, not bind-mounted — to change deps: edit on host → `docker compose cp` → `composer install` (preserves dev deps). `phpunit.xml` is now mounted. Tests use `RefreshDatabase` against the container MySQL, so the dev DB schema is recreated per test run.

## How to run

```bash
# train models from database/ml/section_lines.json (seeded, reproducible)
docker compose exec -T app php artisan ml:train

# mine the 246 test fixtures for weak labels, then train (capped at 150/label — the
# largest cap that still loads under the app's 256 MB limit)
docker compose exec -T app php artisan ml:train --from-fixtures

# inspect vocabulary size, accuracy, drift, and model health
docker compose exec -T app php artisan ml:status

# compare dictionary vs ML across the 246 fixtures
docker compose exec -T app php artisan ml:diagnose

# full test suite (uses container MySQL)
docker compose exec -T app php vendor/bin/phpunit
```