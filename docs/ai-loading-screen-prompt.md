# UjwolBato — AI Loading Screen & UX Improvement Prompts

**System:** CareerGuide AI (*UjjwolBato*) — AI-assisted career guidance with ATS resume
scoring, skill-gap analysis, learning roadmaps, job matching, and career recommendation.

**Stack:** Laravel 13.8 (PHP 8.3) · Inertia 2 + Vue 3 (`<script setup>`, no TypeScript) ·
Tailwind v3 with class-based dark mode · Vite 8

---

## How to use this document

Five **independent** workstreams. Each prompt below is self-contained and can be pasted
into an AI coding tool on its own, in any order, or all at once. They share conventions
but do not depend on each other.

| # | Workstream | Layer | Primary files |
| --- | --- | --- | --- |
| 1 | AI loading screen | Frontend only | `AiLoadingOverlay.vue`, `useAsyncStatus.js`, 6 pages |
| 2 | Career-scoped role view + roadmap | Backend + Frontend | `RoadmapController`, `CareersController`, `Roadmap/Index.vue`, `Careers/Show.vue` |
| 3 | "Is this a resume?" validation | Backend + Frontend | `ResumeValidator`, `StoreResumeRequest`, `lib/resumeHeuristics.js` |
| 4 | Real-time form validation | Backend + Frontend | `useFormValidation.js`, `useValidatedSubmit.js`, ~25 forms |
| 5 | Sidebar grouping | Frontend only | `AuthenticatedLayout.vue` |

### Shared conventions (apply to every prompt)

- **No new npm dependencies.** There is no component library. Hand-roll with inline SVG
  and existing Tailwind utilities. `package.json` currently has only `chart.js`,
  `vue-chartjs`, and `ziggy-js` as runtime deps.
- **No new PHP packages.** `composer.json` has `laravel/framework`, `sanctum`,
  `socialite`, `inertia-laravel`, `phpword`, `smalot/pdfparser`, `rubix/ml`, `ziggy`.
- **Code style:** 4-space indent in `.vue` and `.js`, PSR-12 in PHP. `@/` import alias
  resolves to `resources/js/`.
- **Dark mode** is class-based (`tailwind.config.js` → `darkMode: 'class'`). Reuse the
  exact dark variant pairs already in the codebase: `dark:bg-gray-800`,
  `dark:text-gray-100`, `dark:border-gray-700`, `dark:bg-indigo-900/40`,
  `dark:text-indigo-300`.
- **Inertia requests** must preserve `preserveScroll: true` and `preserveState: true`
  unless there is a stated reason not to.
- **Test gate:** `php artisan test` and `npm run build` must both pass.
- Uncommitted work is already in flight in `resources/js/Pages/Auth/Register.vue`,
  `app/Http/Controllers/Auth/RegisteredUserController.php`,
  `resources/js/composables/useFormValidation.js`, and
  `tests/Feature/Auth/RegistrationTest.php`. **Build on top of it — do not revert it.**

---

# Prompt 1 — AI Loading Screen

## Context

`POST /recommendations/{rec}/rationale` is fully synchronous. It calls an
OpenAI-compatible LLM endpoint (`app/Services/RecommendationRationalizer.php`) with a
60-second timeout and up to 3 attempts with backoff on 429/503 — so the worst case is
roughly 3 minutes. Today the only feedback is a 3.5px `animate-spin` icon inside the
button. On a slow provider the UI looks frozen and the user clicks repeatedly.

## What to build

### 1. `resources/js/Components/SpinnerIcon.vue` (new)

The same spinner SVG is currently inlined in 5 places: `Dashboard.vue:180`,
`Recommendations/Index.vue:91`, `Recommendations/Index.vue:235`, `Resumes/Index.vue:331`,
`Resumes/Index.vue:384`. Extract one component.

- Props: `size` (default `'h-4 w-4'`), pass through `$attrs` for color.
- Renders the two-path spinner already in use:
  `M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15`
- Keep `animate-spin` on the root. Do not change the visual output.
- Replace the 5 inlined copies with this component.

### 2. `resources/js/Components/AiLoadingOverlay.vue` (new)

A centred modal-style overlay. Props:

| Prop | Type | Default | Purpose |
| --- | --- | --- | --- |
| `visible` | Boolean | — | Controls the overlay |
| `messages` | Array | `[]` | Rotated on a timer |
| `label` | String | `'Working'` | Title line |
| `role` | String | `''` | e.g. `'Data Analyst'`, interpolated into messages |

Requirements:

- **Backdrop:** `fixed inset-0 z-[70] bg-gray-900/50 backdrop-blur-sm`, click to dismiss.
- **Panel:** centred card, `max-w-sm`, matching the `Modal.vue` card styling already in
  the codebase.
- **Indeterminate progress bar** at the top of the panel. A bar that animates its width
  back and forth (e.g. 3s `ease-in-out` infinite alternate), plus a reduced-motion
  fallback:
  ```css
  @media (prefers-reduced-motion: reduce) { .ai-progress { animation: none; width: 100%; opacity: .5 } }
  ```
  Honour `prefers-reduced-motion` for **all** animation in this component.
- **Elapsed time counter**, ticking every second, formatted `0:07`. Use `onUnmounted` to
  clear the interval — no leaks when the page unmounts mid-request.
- **Rotating status copy.** Advance to the next message every 2.5s, clamped to the last
  message. Animate the swap with a `<Transition>` (fade + slight upward translate,
  ~200ms). Only the visible message is in the DOM.
- **Wait hint** under the copy, e.g. `This usually takes a few seconds. Don't close this
  page.`
- **Dismiss button** labelled `Continue in the background`. Clicking it hides the
  overlay. **This must NOT cancel the request** — Inertia 2 has no request-cancel API and
  the server call is already in flight. The result still arrives and the normal flash
  toast still fires. The label and tooltip must make that clear.
- **Accessibility:** panel is `role="status"` with `aria-live="polite"` and
  `aria-atomic="true"`. Focus moves to the dismiss button on open and returns to the
  previously focused element on close. The dismiss button must be keyboard reachable.
  Trap nothing — a single button is fine.
- Respect `visible` as a strict prop (no internal `v-model`).

### 3. `resources/js/composables/useAsyncStatus.js` (new)

Reusable state wrapper so pages don't each hand-roll timers.

```js
const { visible, label, messages, role, elapsed, run } = useAsyncStatus();

await run(async () => {
    // the Inertia request
}, {
    label: 'Generating your explanation',
    role: rec.title,
    messages: [...],
});
```

- `run(fn, opts)` sets `visible = true`, resets `startedAt`, starts the 1s elapsed
  ticker, awaits `fn`, then hides.
- **Minimum visible duration of 400ms.** If the provider returns in 120ms the overlay
  would otherwise flash and read as a glitch. Keep it up until both the promise settles
  **and** 400ms have elapsed.
- Always clean up the interval in a `finally`.
- If `run` is called while another `run` is in flight, do nothing. The existing buttons
  already guard on this, but make the composable safe anyway.

### 4. The copy (use verbatim)

**AI explanation generation** (the only flow that actually calls the LLM):

```
label:    'Generating your explanation'
messages: [
  'Sending your profile to the AI…',
  'Reading your skills against {role}…',
  'Writing your explanation…',
  'Almost there — polishing the wording…',
  'Taking longer than usual. This can take up to a minute.',
]
hint:     "This usually takes a few seconds. Don't close this page."
```

Use the ellipsis character `…`, not three dots. Substitute `{role}` with the role title.
Do not promise a shorter wait than the server can actually deliver — the real ceiling is
60s × 3 attempts, so the 5th message must be a reassurance, not a countdown.

**Regenerate recommendations** (`label: 'Rebuilding your recommendations'`) — this is a
pure DB job, **not** an LLM call, so the copy must NOT mention the AI:

```
'Ranking roles against your skills…',
'Scoring skill overlap…',
'Adding a career-path bonus…',
'Almost there…',
```

**Running a skill-gap assessment** (`label: 'Running your skill-gap assessment'`) — also
**not** an LLM call:

```
'Loading your skills…',
'Comparing against the role requirements…',
'Scoring each gap…',
'Finding learning resources…',
'Almost there…',
```

Getting this distinction right matters: only `explain()` calls the LLM, and claiming
otherwise would be a lie in the UI.

## Wire-up

Replace the existing inline spinners with the overlay. In every case keep the existing
guards (`generatingId !== null`, `regenerating`, `runForm.processing`) so double-submits
stay impossible.

| File | Function | Action |
| --- | --- | --- |
| `resources/js/Pages/Recommendations/Index.vue:52` | `explain(id)` | Primary case. Wrap the `router.post` in `run()`. Keep the inline button spinner too (it's a good local affordance) — the overlay is the long-wait signal. |
| `resources/js/Pages/Recommendations/Index.vue:40` | `regenerate()` | Use the regenerate copy. |
| `resources/js/Pages/Recommendations/Index.vue:18-32` | `onMounted` auto-generate | Already renders a skeleton + spinner at `:163-186`. Either show the overlay too or leave the skeleton — pick one and make it consistent. |
| `resources/js/Pages/Dashboard.vue:180` | regenerate | Same as above. |
| `resources/js/Pages/Careers/Show.vue:186` | `assessment.run` POST | Assessment copy. Note this is a `<Link method="post" as="button">`, so you will likely need to convert it to a `router.post` to get `onFinish` callbacks. |
| `resources/js/Pages/Roadmap/Index.vue:40` | `submitRun()` | Assessment copy. Fires from three call sites: `:108`, `:128`, `:167`. |

## Constraints

- Do **NOT** add npm packages.
- Do **NOT** change any controller, service, or route. This is frontend-only.
- Do **NOT** change the Inertia progress bar config in `resources/js/app.js:24-26`.
- Do **NOT** change the flash-message behaviour in `RecommendationController@rationale`.
  It already distinguishes LLM success / concurrent lock / provider failure / disabled
  (`RecommendationController.php:149-168`). The overlay must not swallow or duplicate
  those toasts.
- Do **NOT** hide the page content behind the overlay permanently — dismissal must fully
  restore it.

## Acceptance criteria

1. Clicking "Explain with AI" shows the overlay within 100ms, before the network call
   resolves.
2. Status copy rotates through all messages over ~12s; after the last message it stays put
   and does not loop.
3. The elapsed counter ticks once per second and reaches the real wall-clock time.
4. A response arriving in <400ms still shows the overlay for the full 400ms minimum.
5. "Continue in the background" hides the overlay, the request continues, and the flash
   toast still appears on completion.
6. Keyboard-only: the dismiss button is focusable and activatable; focus returns to the
   trigger.
7. With `prefers-reduced-motion: reduce`, the progress bar stops animating and shows a
   static full-width bar; no motion anywhere in the component.
8. Dark mode: panel, text, and borders readable at every dark step used in the repo.
9. Rapid triple-click on "Explain with AI" produces exactly one request.
10. `npm run build` succeeds with no new warnings.

---

# Prompt 2 — Career-Scoped View Role + Roadmap

## Context

The roadmap is not career-aware, which makes it much less useful than it looks:

- `Recommendations/Index.vue:267` links "Build roadmap" to `route('roadmap.index')` with
  **no role id**. The career context is silently discarded.
- `RoadmapController.php:30` loads only `->latest()->first()`. Running an assessment for
  a different career replaces your view entirely; there is no way back to the previous
  one.
- `Careers/Show.vue` shows role requirements but nothing about *you* — no match score, no
  readiness, no gaps for that specific role.
- `Roadmap/Index.vue` has **zero** client-side validation on the role selector.

## What to build

### 1. `app/Http/Controllers/RoadmapController.php@index`

- Accept a `?role={id}` query parameter, validated with `exists:career_roles,id`.
- Load **all** assessments for the profile cheaply — `id`, `target_role_id`,
  `overall_readiness_score`, `created_at`, and a gap-item count — and pass them as an
  `assessments[]` prop for a switcher.
- Resolve the active assessment: the one whose `target_role_id` matches `?role=`, else the
  latest. Pass its id as `selectedAssessmentId`.
- Load `items` (with `skill` and `recommendations.learningResource`) **only for the
  active** assessment, using the same ordering as today:
  `FIELD(priority, 'high', 'medium', 'low')` then `gap_size desc`.
- Apply the `isStale` fingerprint check (`RoadmapController.php:72-78`) to the
  **selected** assessment only, not the latest.
- Derive `stats` (`readiness`, `gapCount`, `inProgress`, `completed`, `targetRole`,
  `targetRoleId`) from the selected assessment.

Watch the N+1: the `assessments[]` list must not eager-load `items`.

### 2. `resources/js/Pages/Roadmap/Index.vue`

- **Role switcher** across `assessments[]` — pill or tab list showing role title,
  readiness, and date, with the active one highlighted. Navigates via
  `router.get(route('roadmap.index'), { role: id }, { preserveState: true,
  preserveScroll: true })`.
- Change the "Target career role" control from *run-and-overwrite* to
  *navigate-then-confirm*, so switching is a visible, reversible act.
- Per-item card header: add a "View role" link → `careers.show`.
- Add client validation for the role selector.
- Empty state (`!hasAssessment`) still needs to work when `assessments` is empty.

### 3. `app/Http/Controllers/CareersController.php@show`

Add a `fit` block to the Inertia payload:

- `matchScore` + `matched`/`missing` skills from the user's `CareerRecommendation` for
  this role, computed with `RoleMatchScorer::overlap()` — the same helper
  `RecommendationController@index:55` uses.
- `readiness` + `topGaps` from the latest `GapAssessment` for this profile with
  `target_role_id === $careerRole->id`.
- `hasAssessment` (bool) so the CTA can switch.

### 4. `resources/js/Pages/Careers/Show.vue`

New **"Your fit for this role"** panel, replacing the bare CTA block at `:182-189`:

- Match score badge, reusing the `scoreColor()` thresholds already in
  `Recommendations/Index.vue:34` (green ≥70, amber ≥40, red below) — extract to
  `resources/js/lib/format.js` so both pages share one implementation.
- Matched skills as emerald chips, growth areas as amber chips, matching the existing
  card markup.
- Readiness figure when an assessment exists.
- CTA reads **"View your roadmap for this role"** (→ `roadmap.index?role={id}`) when an
  assessment exists, and **"Build a roadmap for this role"** (→ POST
  `assessment.run` with `target_role_id`) when it does not.
- When the user has no skills yet, show a link to `profile.edit` instead of a 0% score.

### 5. `resources/js/Pages/Careers/Index.vue`

Add a "Your match" badge to each role card at `:63` when a `CareerRecommendation`
exists for that role. Requires `RecommendationController`-style data in
`CareersController@index` — keep the query to a single `whereIn('career_role_id', ...)`
on the paginated ids, not an N+1.

### 6. `resources/js/Pages/Recommendations/Index.vue:267`

Change "Build roadmap" to `route('roadmap.index', { role: rec.role_id })`. Label it
"Build roadmap" only when no assessment exists for that role, otherwise "View roadmap" —
pass a per-role `hasAssessment` map in the `index` payload.

## Constraints

- Do **NOT** change the `GapAssessment` schema. All assessments are already persisted —
  this is purely a read-path change.
- `SkillGapAnalyzer::run()` already purges stale progress tracking
  (`purgeStaleProgress`), so re-running for the same role is safe.
- Do **NOT** remove the existing `isStale` warning UI at `Roadmap/Index.vue:117-134`.
- Preserve the `FIELD(priority, ...)` MySQL-specific ordering, or the equivalent
  portable `CASE` expression if you need DB-agnostic ordering.

## Acceptance criteria

1. Clicking "Build roadmap" on a recommendation lands on a roadmap scoped to that role.
2. Assessing two different careers and switching between them via the switcher shows
   each one's correct gaps, resources, and readiness.
3. Reloading `/roadmap?role={id}` directly restores the same scoped view.
4. An invalid `?role=99999` returns a 404, not a 500 or a silent fallback.
5. `?role=` pointing at a role with no assessment shows the empty state with that role
   preselected, not the previous career's data.
6. The `isStale` banner appears only for the currently selected assessment.
7. No N+1 queries — verify with `DB::enableQueryLog()` on the roadmap page.
8. `php artisan test` and `npm run build` pass.

---

# Prompt 3 — "Is This a Resume?" Upload Validation

## Context

Today `ResumeController@store` accepts anything with a `.txt` / `.pdf` / `.docx`
extension. A cover letter, a brochure, a lecture transcript, or a random PDF all get
stored, parsed into bogus sections, and can inject false-positive "skills" onto the
user's profile via `ResumeParser::parseAndStore()`. That pollutes the match scores
downstream.

The only content checks today are "text is not empty"
(`ResumeController.php:92-94`) and, indirectly, "no skills were detected" — which is a
*post-hoc* warning after the damage is done, not a gate.

## What to build

### 1. `app/Services/ResumeValidator.php` (new)

`assess(string $rawText): array` returning:

```php
[
    'is_resume'  => bool,
    'score'      => int,   // 0..N
    'band'       => 'strong'|'borderline'|'not_a_resume',
    'signals'    => string[],  // signals found
    'reasons'    => string[],  // human-readable explanation of failure
]
```

**Weighted signals** (tune so a minimal but real resume clears `strong`):

| Signal | Points | Notes |
| --- | --- | --- |
| Section heading | ~2 each | experience, education, skills, summary, objective, profile, projects, certifications, awards, publications, volunteer, languages, interests, contact. Match case-insensitively on a line that is short and heading-like. |
| Email address | 2 | Reuse the pattern from `resources/js/composables/useFormValidation.js:19` |
| Phone number | 2 | Loose international pattern |
| Date range | 2 | `2019 – Present`, `2019-2021`, etc. |
| Bullet marker | 1 each (cap 3) | Lines starting with `•`, `-`, `*`, `–` |
| Action verb | 1 (cap 2) | managed, led, built, developed, implemented, designed, delivered, improved, reduced, increased, launched, owned… |
| **Prose penalty** | −2 | Average line length > 200 chars with < 3 bullets |

**Hard failure** independent of score: fewer than 200 characters of extracted text.

### 2. `config/resume.php` (new)

Tunable thresholds, so admins/devs can loosen the gate without a code change:

```php
return [
    'strong_threshold'    => 6,
    'borderline_threshold'=> 3,
    'min_characters'      => 200,
    'max_characters'      => 20000,   // already implied by the 50k request cap
];
```

### 3. Bands (score + warn)

Per the agreed behaviour — **never silently reject a valid resume**:

| Band | Score | Behaviour |
| --- | --- | --- |
| `strong` | ≥ 6 | Accept silently. No UI change. |
| `borderline` | 3–5 | Accept, and show a non-blocking amber note on the result. |
| `not_a_resume` | < 3 (or < 200 chars) | **Reject.** Delete the stored file, return a validation error on `resume_file`. |

The rejection message must name what was missing, e.g.:
> That file doesn't look like a resume — we couldn't find any experience, education, or
> skills sections. Check you uploaded the right document.

### 4. `app/Http/Requests/StoreResumeRequest.php` (new)

`ResumeController@store` is currently the **only** controller in the app still using
inline `$request->validate()` (`:54-62`). Move it to a FormRequest for consistency with
the other 23.

- `resume_file`: `nullable`, `file`, `mimes:txt,pdf,docx`, `mimetypes:` (add MIME checking
  on top of extension checking — this is currently missing), `max:5120`
- `content`: `nullable`, `string`, `max:50000`
- `after()` hook enforcing at least one of the two, replacing the manual flash at
  `ResumeController.php:64-66` with a **real validation error** that the existing
  `<InputError :message="uploadForm.errors.resume_file" />` renders inline.

Use the same `SUPPORTED_EXTENSIONS` constant from `ResumeTextExtractor` so the accepted
list has one source of truth.

### 5. `app/Http/Controllers/ResumeController.php@store`

- After `ResumeTextExtractor::extract()` (or `clean()` on the paste path), call
  `ResumeValidator::assess($rawText)`.
- `not_a_resume` → `Storage::disk('local')->delete($stored)` then
  `back()->withErrors(['resume_file' => $message])`. Mirror the existing cleanup
  pattern at `:81-85`.
- `borderline` → accept, but stash the verdict in session so the page can show the note.
  Pass it to the view as a prop in `ResumeController@index` (read it via
  `session('resume_likeness')` and pull it out in the controller so the front end stays
  stateless).
- `strong` → accept, no change.

### 6. Client: `resources/js/lib/resumeHeuristics.js` (new)

Mirror the server signals for an instant pre-upload check.

- **`.txt` files:** `FileReader` the first ~8 KB and run the same heading/contact check
  in JS. The user learns it's wrong *before* uploading.
- **`.pdf` / `.docx`:** cannot be sniffed without shipping a parsing library. Check
  metadata only (extension, size) and let the server stay authoritative. Do not add a
  library just for this.

### 7. Client: new `rules.file()` in `useFormValidation.js`

```js
file({ extensions = [], maxBytes = null, mimeTypes = [] }, message)
```

Then wire into both upload surfaces:

- `resources/js/Pages/Resumes/Index.vue:95` `fileSelected()`
- `resources/js/Pages/Dashboard/Partials/OnboardingWizard.vue:46` `uploadFile()`

Both should show the error immediately on `@change`, not on submit.

## Tests

- **`tests/Unit/ResumeValidatorTest.php`** (new) — each signal in isolation, the prose
  penalty, the <200 char hard fail, and the three bands.
- **`tests/Feature/Resume/ResumeUploadTest.php`** (extend) — reject a non-resume, accept
  a borderline one, assert the stored file is deleted on rejection, and assert a clean
  upload still creates zero errors.

**Regression risk — verify these still pass.** Every fixture in `tests/Fixtures/files/`
goes through `resume.store`:

- `upload-sample.txt` — has summary / experience / education / skills plus a `2019 -
  Present` date range. Should score `strong`.
- `upload-sample.pdf`, `upload-sample.docx` — same content, should score `strong`.
- `ats-cv-1-senior-laravel.txt`, `ats-cv-2-react-frontend.txt`,
  `ats-cv-3-ml-engineer.txt`, `ats-cv-4-mismatch-weak.txt`,
  `ats-cv-5-verbose-dense.txt` — **check these individually.** `ats-cv-4-mismatch-weak`
  in particular is deliberately sparse and may fall into `borderline`. If a fixture
  scores `not_a_resume`, either adjust the fixture or lower the threshold — do **not**
  weaken the gate silently.

## Acceptance criteria

1. Uploading a plain paragraph of prose is rejected with a message naming what was
   missing, and no `Resume` row is created.
2. The rejected file is deleted from the `local` disk — no orphaned files.
3. The error renders **inline** under the file input via `<InputError>`, not only as a
   toast.
4. A valid resume with only `.txt` and no skills sections still uploads successfully.
5. A `.txt` file that is not a resume is caught **client-side**, before any network
   request.
6. A file with a `.pdf` extension but a non-PDF MIME type is rejected.
7. Borderline uploads succeed and show the amber note.
8. All existing resume tests pass unchanged.
9. `php artisan test` and `npm run build` pass.

---

# Prompt 4 — Real-Time Client & Server Form Validation

## Context

Confirmed root cause: `validate()` in `resources/js/composables/useFormValidation.js:128`
is only ever called **inside submit handlers**. Nothing validates on blur or on input, so
the user sees nothing until they click submit. The composable already has 16 well-built
rules and correctly writes to `form.errors` via `setError` — the plumbing is there, the
*trigger* is missing.

On the server side, the 4 remaining inline `$request->validate()` calls and the manual
flash at `ResumeController.php:64-66` mean some failures never render inline at all.

## What to build

### 1. Extend `resources/js/composables/useFormValidation.js`

- **`validateField(form, field, rules)`** — run one field's rules, returns the message or
  `null`. Refactor `validate()` to call it internally so there is one implementation.
- **`attach(form, rulesMap)`** → returns `{ onBlur, onInput }` handlers to spread onto
  inputs. Behaviour:
  - validate on `blur`
  - once a field has errored, re-validate on every `input` so the error clears live
  - once valid, stop re-validating (don't nag mid-typing)
- **`focusFirstError(form, container)`** — scroll to and focus the first
  `[data-error]` field.
- **`errorSummary(form)`** — count + list of active errors.
- **`rules.file({ extensions, maxBytes, mimeTypes })`** and **`rules.minFileBytes`**.

### 2. `resources/js/Components/FormErrorSummary.vue` (new)

Top-of-form alert: "2 problems below" with anchor links that scroll to each field. Red
border, `role="alert"`, dark-mode variants matching the repo.

### 3. `resources/js/composables/useValidatedSubmit.js` (new)

Wraps `validate()` + submit + `onError` (toast the first server message) +
`focusFirstError()`, so a 422 can never fail silently. This is the piece that makes
server-side validation visible to the user.

### 4. Apply to every form

| Area | Files |
| --- | --- |
| Auth | `Login.vue`, `Register.vue`, `ForgotPassword.vue`, `ResetPassword.vue`, `ConfirmPassword.vue`, `VerifyEmail.vue` |
| Profile partials | `UpdateProfileInformationForm.vue`, `UpdatePasswordForm.vue`, `DeleteUserForm.vue`, `CareerProfileForm.vue`, `EducationForm.vue`, `UserSkillsForm.vue`, `WorkExperienceForm.vue` |
| Resume | `Resumes/Index.vue`, `Dashboard/Partials/OnboardingWizard.vue` |
| Roadmap | `Roadmap/Index.vue` — role selector currently has **no** client rule at all |
| Admin CRUD | `Settings/Index.vue`, `Skills/Index.vue` (CSV import), `Users/Index.vue`, `JobPostings/Index.vue`, `LearningResources/Index.vue`, `SkillCategories/Index.vue`, `CareerRoles/Index.vue` |

Add `data-error` to every field that can fail, and audit for any field that is missing an
`<InputError>` slot.

### 5. Server side

- New FormRequests for the 4 remaining inline validators, bringing the codebase to 100%
  FormRequest coverage:
  - `StoreResumeRequest` (`ResumeController.php:54`)
  - `StoreAtsScanRequest` (`ResumeController.php:186`)
  - `StoreGapAssessmentRequest` (`GapAssessmentController.php:16`)
  - `RoadmapProgressUpdateRequest` (`RoadmapController.php:100`)
- Replace the manual flash at `ResumeController.php:64-66` with a real validation error
  (see Prompt 3 — do this once, not twice).
- Note that `Admin/UpdateSettingsRequest.php:38-53` already has a good `after()`
  cross-field pattern for the weight-sum-must-equal-100 rules. Follow it for any new
  cross-field validation rather than inventing a new style.

## Constraints

- Do **NOT** add a validation library. The composable is dependency-free by design and
  should stay that way.
- Do **NOT** remove the `clearErrors()` call at the top of `validate()` — it clears stale
  server errors before client rules run.
- Keep the rule-factory signature `(value, form) => message | null`. Existing cross-field
  rules (`confirmed`, `afterOrEqualField`, `eitherOr`) depend on it.
- Every rule short-circuits on empty values, so `rules.required()` must always be listed
  **first** in a field's rule array. Preserve that invariant.

## Acceptance criteria

1. Focusing and blurring an empty required field shows its error without any submit.
2. Typing into a field that is already showing an error clears it as soon as the value
   becomes valid.
3. Focusing a field never shows an error — errors appear on blur or submit, not on focus.
4. A field that is valid does not re-validate on every keystroke.
5. Submitting an invalid form focuses the first failing field and scrolls it into view.
6. `FormErrorSummary` shows the correct count and clicking a link scrolls to the field.
7. A server-side 422 (e.g. a rule with no client equivalent) surfaces visibly — as an
   inline error and a toast — rather than silently doing nothing.
8. Dark mode is correct on the summary component.
9. `php artisan test` and `npm run build` pass.

---

# Prompt 5 — Sidebar Grouping by Priority

## Context

`resources/js/Layouts/AuthenticatedLayout.vue:34-57` defines `navLinks` as a flat array
ordered somewhat arbitrarily. The order is not alphabetical and not meaningfully
prioritised — `Jobs` sits between `Roadmap` and `Resumes`, breaking the natural
"plan then act" flow.

Separately, **Admin → Settings is missing from the sidebar entirely.** It is reachable
only through the user dropdown (`:203-208`) or by typing the URL, which is why the
settings page is easy to overlook.

## Decision: grouped by priority, alphabetical as a tiebreak

Pure alphabetical was considered and rejected. This is a workflow application where
"recommend a career" and "run a gap assessment" are the point; burying them in an A–Z
list would hide the core flow. Group headings give predictable structure, and
alphabetical ordering within a group keeps it stable as items are added.

## What to build

Replace `navLinks` with `navSections` — an array of `{ heading, links }`.

### User sections

| Heading | Links |
| --- | --- |
| **Overview** | Dashboard |
| **Your plan** | Recommendations, Roadmap |
| **Explore** | Careers, Jobs, Skills, Learning |
| **Documents** | Resumes |

### Admin sections

| Heading | Links |
| --- | --- |
| **Overview** | Dashboard, Reports |
| **People** | Users |
| **Catalog** | Career Roles, Job Postings, Learning Resources, Skill Categories, Skills *(alphabetical)* |
| **Settings** | Settings |

### Rendering

- Heading markup: `px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider
  text-gray-400 dark:text-gray-500`.
- Only render a heading if its group has links — defensive, and keeps the markup correct
  if a role's sections change.
- `space-y-1` between links within a group, `space-y-2` between groups.
- Add a `settings` (cog) icon to the `icons` map at `:19-32`.
- The nav container already has `overflow-y-auto` (`:142`), so the extra height is fine
  at `w-64` on short viewports — verify on a 768px-tall screen.
- Keep the existing `NavLink` active state (`route().current(link.route)`) — it needs no
  change.
- Add **Admin → Settings** to the sidebar and remove the duplicate entry from the user
  dropdown (`:203-208`) so there is one source of truth.

## Constraints

- Do **NOT** change `NavLink.vue` — it already handles active styling and
  `aria-current="page"`.
- Preserve the existing mobile drawer behaviour: overlay, `resize` listener that
  auto-closes below `640px`, and the `onBeforeUnmount` cleanup at `:76-82`.
- Keep the icons map inline in the layout. Do not add an icon library for one more icon.
- Dark mode must match the existing heading and link pairs exactly.

## Acceptance criteria

1. The user sidebar renders four labelled groups in the order above.
2. The admin sidebar renders four labelled groups in the order above.
3. Admin → Settings is reachable from the sidebar and no longer duplicated in the user
   dropdown.
4. The active route is still highlighted correctly in both roles.
5. The mobile drawer still opens, closes, and auto-collapses below 640px.
6. Nothing overflows or gets clipped at 768px viewport height with the admin sidebar.
7. Dark mode is correct for headings, active links, and hover states.
8. Keyboard navigation order follows visual order (groups are not `display: none` or
   `visibility: hidden`).
9. `npm run build` passes.

---

# Shared Verification

Run for every workstream:

```bash
php artisan test
npm run build
```

**Test files most likely to need new cases:**

| File | Workstream |
| --- | --- |
| `tests/Feature/Resume/ResumeUploadTest.php` | 3 |
| `tests/Unit/ResumeValidatorTest.php` *(new)* | 3 |
| `tests/Feature/AtsScannerTest.php` | 3 (fixtures flow through `resume.store`) |
| `tests/Feature/RecommendationControllerTest.php` | 1 (frontend only, but confirm no regression) |
| `tests/Feature/Ml/ResumeSegmenterTest.php` | 3 (confirm the new gate doesn't sit in the parse path) |

**Important:** `ResumeValidator` must gate the **upload endpoint only**, not
`ResumeParser::parseAndStore()`. Tests and seeders that construct `Resume` models
directly must keep working.

**Manual smoke pass:**

1. AI explain → overlay appears, copy rotates, toast arrives, dismissal works.
2. Recommendation → Build roadmap → lands scoped to that role.
3. Switch careers in the roadmap switcher → correct data each time.
4. Upload a prose PDF → rejected inline, file not left on disk.
5. Upload a real resume → succeeds.
6. Blur an empty required field on the registration form → error without submit.
7. Submit an invalid form → first bad field focused.
8. Admin sidebar → all four groups, Settings present.
9. Repeat 1–8 with dark mode on and a keyboard only.

## Suggested commit order

One commit per workstream, in numeric order, so any single one can be reverted without
stranding the others:

1. `feat(ui): add AI loading overlay with rotating status copy`
2. `feat(roadmap): scope roadmap and role view to the selected career`
3. `feat(resume): validate uploads are actually resumes`
4. `feat(validation): add real-time client validation and complete FormRequest coverage`
5. `refactor(ui): group sidebar navigation by priority`
