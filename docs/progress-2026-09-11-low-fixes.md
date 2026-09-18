# CareerGuidance — Audit Low-tier polish batch (Fri 11 Sep 2026)

> Scope: close out the remaining **Low** items (#24–#36) from `docs/product-audit.md`.
> The 8 High items were resolved 2026-09-09 and the 15 Medium items were resolved earlier
> today (2026-09-11). This pass fixes the 12 actionable Low items and documents the one
> deliberate deferral (#33). All changes are small and verified; nothing touches the ML core.

## 1. What was done

| # | Item | File(s) | Detail |
|---|---|---|---|
| 24 | Zero stats above empty state | `resources/js/Pages/Dashboard.vue` | Stats grid now renders only when `hasData`; on first visit a 4-card `animate-pulse` skeleton row is shown instead of zeros |
| 25 | Empty states only link to profile | `resources/js/Pages/Recommendations/Index.vue`, `resources/js/Pages/Jobs/Index.vue` | Added secondary "Upload a resume instead" CTA → `resume.index` (resume upload is the faster bootstrap path) |
| 26 | ATS weight hints hardcoded | `app/Services/AtsScanner.php`, `app/Http/Controllers/AtsScanController.php`, `resources/js/Pages/AtsScans/Show.vue` | `weights()` is now public (reads `Settings::get('ats_weights')`, defaults 50/15/15/20, normalizes to 100). Controller passes real weights in the `scan` payload; the component renders `N% weight` hints from them with defaults as fallback |
| 27 | No way to add job postings | `resources/js/Pages/Resumes/Index.vue` | When no job postings exist, admins now get an "Add one" link → `admin.job-postings.index` (add is the inline modal there); other roles keep the plain notice |
| 28 | Status toggle full reload | `resources/js/Pages/Roadmap/Index.vue` | Optimistic status update — a reactive `optimisticStatuses` map flips the button instantly, `router.patch` runs in background with `preserveScroll`, entry is dropped on success/error so the server response reconciles |
| 29 | Re-run can't pick a new target role | `app/Http/Controllers/RoadmapController.php`, `resources/js/Pages/Roadmap/Index.vue` | `stats.targetRoleId` passed from the assessment; the "Re-run assessment" card is now a form with a target-role select (pre-filled with current role; "Auto (best match)" option) and a disabled/spinner-aware submit; the stale-notice re-run uses the same form |
| 30 | No skeleton loaders | `Dashboard.vue`, `Recommendations/Index.vue`, `Jobs/Index.vue` | Lightweight `animate-pulse` placeholder cards on the first-visit / zero-skill states (Inertia pages render server-side, so this addresses the aesthetic affordance, not a loading gap) |
| 31 | Welcome/auth lack dark mode | `resources/js/Pages/Welcome.vue`, `resources/js/Pages/Auth/{Login,Register,ForgotPassword,ResetPassword,ConfirmPassword,VerifyEmail}.vue` | `dark:` variants across hero/feature cards, headings, dividers, Google button, links, status boxes, and footer |
| 32 | No delete confirmation | `resources/js/Pages/Resumes/Index.vue` | Delete now opens a confirmation `Modal` (matches the admin delete-confirm pattern) before `router.delete` |
| 33 | `embedding` columns dead | none | **Deferred** — documented seam; the operative semantic tier is deterministic TF-IDF cosine in `SkillSimilarity`. See audit note |
| 34 | `InputError` keeps empty DOM nodes | `resources/js/Components/InputError.vue` | `v-show` → `v-if` (no more empty error elements for screen readers) |
| 35 | `TextInput` lacks `id`/`type` props | `resources/js/Components/TextInput.vue` | Explicit `id` + `type` (default `'text'`) props bound to the input; existing callers unaffected |
| 36 | `clean()` keeps non-Latin scripts | `app/Services/ResumeTextExtractor.php`, `tests/Feature/Resume/ResumeParserTest.php` | `clean()` strips Cyrillic/Arabic/Devanagari/CJK/Hangul/Thai/etc. ranges before parsing to avoid skill-extraction false positives; regression test added |

## 2. Notes / decisions

- #26 keeps a single source of truth: `AtsScanner::weights()` (settings-backed) is what the
  UI now displays, so weight hints can never drift from the scoring math.
- #27 is gated on `auth.user.role === 'admin'`; job posting creation pages sit behind
  `role:admin` middleware, so a non-admin link would 403.
- #28/#29 reuse the existing `runForm`/`router.patch` patterns; no new endpoints.
- #36 uses explicit Unicode range blocks (not a broad "remove non-Latin" negation) so digits,
  punctuation, and Latin text are untouched.
- Pint autofixed `AtsScanController.php` and `ResumeTextExtractor.php` to repo style
  (imports + `new Foo` without parens); no logic change. `AtsScanner.php` pre-existing
  violations (`concat_space`, `not_operator_with_successor_space`) are untouched baseline.

## 3. Verification

- **Full suite:** 83/84 green in the container MySQL
  (`docker compose exec -T app php vendor/bin/phpunit`, ~2:18). Only failure remains the
  pre-existing `Tests\Feature\Ml\ExampleResumeTest::test_ml_rescues_unsegmented_docx_when_models_ready`
  (ML models not trained). New `test_clean_strips_non_latin_scripts` passes.
- **Frontend:** `npm run build` succeeds.
- **Style:** `vendor/bin/pint --test` clean on all touched PHP files except the pre-existing
  baseline in `AtsScanner.php`.

## 4. Environment notes

- Same recurring env as previous phases: docker compose (app, mysql on 3307, nginx 8082, vite,
  mailpit, scheduler). PHPUnit wipes the dev DB per run (`RefreshDatabase`); restore with
  `migrate:fresh --seed --force` if you need seeded data.