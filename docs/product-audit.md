# Product Audit — CareerGuide AI ("Ujjwol Bato")

> Audit date: 2026-09-09. Reviewer scope: UX design, ML-driven recommendation systems,
> full-stack web applications (Laravel + Vue / Inertia).

## Summary verdict

**Partially working as intended — High-severity issues resolved (2026-09-09).**

The deterministic recommendation core (skill overlap + TF-IDF cosine + career-path BFS)
works, is explainable, and is well-tested (69 tests green). All 8 High issues below were
fixed and re-verified on the audit date; Medium and Low items remain open.

Three structural problems originally identified:

1. **The system overview misdescribes the ML.** K-Means and Random Forest are **not** used
   for user/role clustering or job-fit/recommendation prediction. They are **resume-section
   parsers** (labeling lines like "experience"/"skills" to split an uploaded resume). Job
   matching and recommendations are 100% deterministic math. The only real ML runs at
   ~20% holdout accuracy and has **no retraining or drift loop**.
2. **Feedback is largely invisible.** Flash messages (success/error) from 31+ controller
   flows are never rendered in the Vue frontend. Uploads, assessments, AI rationale, and
   admin CRUD all complete silently.
3. **Cold start and first-run contradict the stated intent.** There is **no "upload a
   resume" option in onboarding** (manual entry only), and the wizard's value proposition
   is thin.

---

## ML reality check

| Claim in overview | Reality | Evidence |
|---|---|---|
| "K-Means groups users/job roles/skills into clusters" | **False.** K-Means (k=6) clusters **resume text lines** to smooth section-heading predictions. | `app/Services/Ml/SectionClusterer.php`, `app/Services/Ml/ResumeSegmenter.php:177-224`, `config/ml.php` |
| "Random Forest predicts job fit / career path / ranking" | **False.** RF (500 trees) classifies **resume lines into 13 section labels**. Recommendations use deterministic overlap 60% + TF-IDF cosine 40% + BFS path bonus. | `app/Services/Ml/SectionClassifier.php`, `app/Services/RecommendationEngine.php:59`, `app/Services/RoleMatchScorer.php:57-60` |
| (Implicit) "ML drives recommendations" | **False.** No trained model is invoked in scoring. `SkillSimilarity` is pure-PHP TF-IDF; `embedding` JSON columns are never populated. | `app/Services/Ml/SkillSimilarity.php` |
| Real classifiers are accurate | **~20% holdout accuracy**, documented. System works only because dictionary matching + heuristics carry it. | `docs/progress-2026-09-07.md:175` |

---

## Issues grouped by severity of change needed

### High — fix first

These block a trustworthy, low-friction first-run and basic feedback loop.

| # | Issue | Tag | Area | Evidence |
|---|---|---|---|---|
| 1 | Onboarding has **no "upload a resume" path**; only a manual 5-step wizard (contradicts product intent of "upload **or** manual entry") | UX | Onboarding | `resources/js/Pages/Dashboard/Partials/OnboardingWizard.vue:14-20` |
| 2 | **Flash messages never rendered** — success/error invisible across resume, scan, recommendations, LLM rationale, and admin CRUD (verified: zero `flash` usage in Vue) | UX | Frontend | all controllers + `app/Http/Middleware/HandleInertiaRequests.php` + all `.vue` |
| 3 | **Cold-start sidebars mislead**: "Priority gaps for your top match" shows random, non-personalized gaps for users with no top match | Backend | Dashboard | `app/Http/Controllers/DashboardController.php:99-100`, `resources/js/Pages/Dashboard.vue:248` |
| 4 | Roadmap "auto-pick (best match)" **silently selects the first alphabetical role** for a zero-skill user, generating a full gap list as if personalized | Backend | Gap Analysis | `app/Services/SkillGapAnalyzer.php:140` |
| 5 | **Gap assessment never auto-refreshes** when skills/role change — no staleness signal; re-run orphans old progress rows and drops the chosen target role | Backend | Gap Analysis | `app/Http/Controllers/RoadmapController.php:18-27`, `app/Services/SkillGapAnalyzer.php:79-83` |
| 6 | **No retraining / drift loop** for ML: no scheduler, no cron, tiny 165-line corpus, ~20% classifier accuracy | Data / ML | ML lifecycle | `app/Console/Commands/MlTrain.php`, `routes/console.php` (absent) |
| 7 | **Accessibility: `InputLabel` never binds `for`** → labels not associated with inputs (breaks screen readers + click-to-focus) | UX / A11y | Components | `resources/js/Components/InputLabel.vue:10` |
| 8 | **Near-zero ARIA** app-wide: icon-only dark-mode toggle, notification bell, hamburger, scroll arrows have no `aria-label`; no `aria-live`; no `aria-expanded` | UX / A11y | Layout | `resources/js/Layouts/AuthenticatedLayout.vue:141,154,207` (only 1 `aria-*` in entire app) |

### High — resolution log (2026-09-09)

All High issues fixed and verified. Verification: 75 phpunit tests green (69 existing + 6 new regression tests in `tests/Feature/AuditHighFixesTest.php`), Laravel Pint clean, `vite build` clean, `docker compose config` valid, scheduler confirmed via `php artisan schedule:list`.

| # | Fixed | What was done |
|---|---|---|
| 1 | ✅ | Onboarding reworked with a mode choice screen — **upload a resume**, manual wizard, or "skip all for now". Upload posts to `resume.store` (multipart). Onboarding auto-dismisses once a resume exists (`HandleInertiaRequests` now checks `profile.resumes`). |
| 2 | ✅ | `HandleInertiaRequests` shares `flash` (success/error/warning/info) to all pages; new `FlashMessage.vue` toast mounted in `AuthenticatedLayout` + `GuestLayout` with `aria-live="polite"`, 6s auto-dismiss, close button. |
| 3 | ✅ | `DashboardController` now passes `gapInsights` — the "Priority gaps for your top match" sidebar renders only when real gap data or a top match score exists; otherwise a "Upload a resume / Add skills" CTA shows. |
| 4 | ✅ | Removed the silent `CareerRole::first()` (alphabetical) fallback in `SkillGapAnalyzer::defaultTargetRole()`. A gap assessment now requires skills **or** an explicit target role, with a clearer error message. Also fixed a latent bug: `target_role_id` was parsed via `integer()` (returns 0 for UUID ids) — now read as string. |
| 5 | ✅ | New `skills_fingerprint` column on `gap_assessments`; `purgeStaleProgress()` cleans orphaned rows on re-run; re-run preserves the previous target role; `RoadmapController` computes `isStale` and the roadmap shows an amber "your skills have changed" banner with a re-run action. |
| 6 | ✅ | Added a `scheduler` service to `docker-compose.yml` running `php artisan schedule:work`; `routes/console.php` schedules `ml:train --include-resumes` daily at 02:30. `MlTrain` now persists training metadata (`trained_at`, corpus size, vocab size, classifier accuracy) via `ModelRepository::saveMeta()`; new `ml:status` command prints it. Shared `app_storage` volume keeps trained models/persist. |
| 7 | ✅ | `InputLabel` now declares a `for` prop and binds `:for`, associating labels with inputs (screen readers + click-to-focus). |
| 8 | ✅ | ARIA pass in `AuthenticatedLayout`: `aria-label` on dark-mode toggle / scroll arrows / hamburger, `aria-expanded` + `aria-controls` + `id` on the responsive menu, `aria-pressed` on the dark toggle; notification bell disabled with fake "3" badge removed; `Modal` uses `aria-modal="true"`. |

### Medium — fix in the same sprint, after High

These degrade trust, actability, and mobile/async quality but don't block first-run.

| # | Issue | Tag | Area | Evidence |
|---|---|---|---|---|
| 9 | Value proposition before data entry is thin ("Help us personalize...") — no output preview, no time-to-value | UX | Onboarding | `OnboardingWizard.vue:146-150` |
| 10 | Onboarding modal **re-appears after a resume upload that extracts 0 skills** (detection ignores resumes) | Backend | Onboarding | `HandleInertiaRequests.php:33-41` |
| 11 | Onboarding "skip" is **session-only** — reappears after logout / session expiry | Backend | Onboarding | `routes/web.php:85` |
| 12 | Every onboarding step POST redirects to `/dashboard` → redundant full Inertia round-trip per step | Backend | Onboarding | `ProfileController.php:50-52`, `UserSkillController.php:27-29`, `EducationController.php:18-20`, `WorkExperienceController.php:18-20` |
| 13 | **No formatting suggestions generated** in ATS — low formatting score but no advice on bullets / action verbs / quantified achievements | ATS | ATS | `app/Services/AtsScanner.php:132-148` vs `writeDetails():194-241` |
| 14 | ATS scan button has **no loading/disabled state** (synchronous scan) | UX | ATS | `resources/js/Pages/Resumes/Index.vue:229-235` |
| 15 | Recommendations hinted as "AI" but explainability is template-only by default (LLM rationale inert behind settings; users can't see *why* deterministically) | ML Logic | Recommendations | `app/Services/RecommendationRationalizer.php` |
| 16 | Re-run assessment orphans old `ProgressTracking` records (no cleanup/migration) | Backend | Gap Analysis | `SkillGapAnalyzer.php:79-83` |
| 17 | "Regenerate" / "Explain with AI" lack robust loading feedback; `preserveState` hides Inertia progress bar; no cancellation | UX | Recommendations | `resources/js/Pages/Recommendations/Index.vue:19-25,58-67` |
| 18 | Notification bell is a **decorative, non-functional button** with hardcoded badge "3" | UX | Layout | `AuthenticatedLayout.vue:154-163` |
| 19 | No global search in navbar (search icon decorative / absent for power users) | UX | Layout | `AuthenticatedLayout.vue:98-135` |
| 20 | Admin/onboarding modal grids use fixed `grid-cols-2` → cramped on narrow phones | UX | Mobile | `Admin/CareerRoles/Index.vue:215`, `JobPostings/Index.vue:210`, `OnboardingWizard.vue:194` |
| 21 | Skills auto-added from resume at proficiency 3 with **no user notification** or explanation | UX | Resume | `app/Services/ResumeParser.php:76-79` |
| 22 | **No feedback when resume parsing extracts zero skills** — "parsed successfully" with nothing found | UX / Backend | Resume | `app/Http/Controllers/ResumeController.php:90`, `Resumes/Index.vue:182` |
| 23 | Cold-start picks an arbitrary role on the roadmap — results presented as personalized | Backend | Gap Analysis | `SkillGapAnalyzer.php:140` (see #4 — same root) |

### Low — polish / nice-to-have

Fix opportunistically; minimal risk.

| # | Issue | Tag | Area | Evidence |
|---|---|---|---|---|
| 24 | Stats cards render **zeros** above the empty state on first visit (depressing first impression) | UX | Dashboard | `resources/js/Pages/Dashboard.vue:66-87` |
| 25 | Recommendations / Jobs empty states only link to profile — never suggest resume upload as the faster path | UX | Empty states | `Recommendations/Index.vue:95`, `Jobs/Index.vue:56` |
| 26 | ATS weight hints are **hardcoded strings** in UI — stale if admin edits `ats_weights` | ATS | ATS | `AtsScans/Show.vue:87-90` |
| 27 | No link to add job postings when none exist to scan against | ATS | ATS | `Resumes/Index.vue:237` |
| 28 | Roadmap status toggle PATCH triggers full page reload (no optimistic UI) | UX | Gap Analysis | `Roadmap/Index.vue:39-42` |
| 29 | Roadmap "re-run" cannot pick a new target role from the results view | UX | Gap Analysis | `Roadmap/Index.vue:121-127` |
| 30 | No skeleton loaders anywhere (acceptable for Inertia data-heavy pages, but no async affordance) | UX | Frontend | global |
| 31 | Welcome and auth pages lack dark-mode variants (rest of app supports dark) | UX | Frontend | `resources/js/Pages/Welcome.vue:18` |
| 32 | No confirmation dialog before deleting a resume | UX | Resume | `Resumes/Index.vue:241-246` |
| 33 | `embedding` JSON columns dead (documented seam) — semantic tier untestable as described | Data | Schema | migrations (skills, job_postings) |
| 34 | `InputError` uses `v-show` → empty error elements remain in DOM for screen readers | UX / A11y | Components | `resources/js/Components/InputError.vue:10` |
| 35 | `TextInput` has no explicit `id`/`type` prop definitions (relies on fragile attr fallthrough) | UX / A11y | Components | `resources/js/Components/TextInput.vue:21-25` |
| 36 | Resume text `clean()` does not strip non-Latin scripts → possible skill-extraction false positives | Backend | Resume | `app/Services/ResumeTextExtractor.php:111-117`, `app/Services/ResumeParser.php:117` |

---

## Top 5 prioritized fixes

1. **App-wide flash/toast system (High).** Share `flash` in `HandleInertiaRequests`, add a toast component reading `usePage().props.flash`, wire `aria-live="polite"`. Restores the feedback loop across all 31+ flows. Fixes #2.
2. **Rework onboarding: resume-upload vs manual choice + value-proposition screen (High).** Add resume upload before Step 1, show concrete "what you'll get", dismiss onboarding once a resume or skills exist, persist skip/completion on the profile. Fixes #1, #9, #10, #11, #12.
3. **Stop misleading cold-start users (High).** Hide/re-label "priority gaps for your top match" with no top match; require an explicit role choice when zero skills; add "upload a resume" CTA to empty states. Fixes #3, #4, #23, #25.
4. **Align the ML story with reality + add a data loop (High, strategic).** Either re-scope messaging to "deterministic skill matching + semantic similarity," or add a scheduled `ml:train` retrain (with `--include-resumes` harvesting), an accuracy/training-date readout, and a simple drift signal. Fixes #6, #15, #33.
5. **Accessibility + assessment freshness (Medium, broad base).** Bind `for` on `InputLabel`, add ARIA labels/`aria-expanded`/`aria-live`; add a "stale assessment" banner + re-run preserving target role + cleanup orphaned progress; add ATS formatting suggestions. Fixes #7, #8, #13, #16, #34, #35.

---

## Suggested UX validation methods

- **Task-based usability testing** (moderated, think-aloud, 5–8 users): sign-up → setup, "why did my resume score X?", "make this gap list an action plan."
- **A/B test onboarding variants**: resume-first vs manual-first vs both. Instrument onboarding completion %, profiles with ≥3 skills, first recommendation viewed, 7-day retention.
- **Five-second test** on onboarding value-prop screen and dashboard (can users recall *what they'll get*?).
- **First-click testing** on Login → Recommendation → Roadmap journey.
- **Recommendation comprehension test** — ask users to explain back why they were matched (validates #15).
- **Funnel analytics**: onboarding completion, resume upload (incl. 0-skill extraction rate), assessment run/re-run, "Explain with AI" CTR, scan completion.
- **Accessibility passes**: axe/Lighthouse, keyboard-only walkthrough, screen reader pass (NVDA/VoiceOver) — validates #7/#8.
- **SUS + CSAT survey** post-first-session; intercept interviews on the ATS results page.