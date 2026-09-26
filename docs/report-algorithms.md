# Algorithms

## Introduction

Ujjwol Bato ("CareerGuide AI") blends a small supervised/unsupervised machine-learning
layer for resume *section segmentation* with a fully deterministic scoring pipeline for
the product's user-facing intelligence: career recommendations, job matching, ATS
scoring, and skill-gap analysis. An optional LLM "explain with AI" step layers natural
language on top of the deterministic rationale; it is inert unless a provider key is
configured.

> **Accuracy note.** The only models in the system are the resume-section Random Forest
> and K-Means. The recommendation, matching, ATS, and gap subsystems are **not**
> model-driven — they are deterministic, explainable math. The shipped model measures
> **17.1%** holdout accuracy (seeded, reproducible — see §1.3), and the classifier is a
> genuine weak signal: 13-way heading classification from a 149-line corpus is close to
> guessing, and ~20–36% is the realistic band. This is acceptable only because
> dictionary/structural heuristics carry resume parsing, and because the ML path is
> additive — it can only refine a dictionary segmentation, never invent a resume.

---

## Section 1 — Machine Learning: resume section classification

### 1.1 Random Forest classifier

**Purpose.** Assign one of 13 section labels (`experience`, `skills`, `education`,
`certifications`, `projects`, `publications`, `awards`, `volunteer`, `languages`,
`interests`, `references`, `summary`, `contact`) to every line of an uploaded resume.

**Configuration.** A Rubix ML `RandomForest` ensemble of **500 CART decision trees**
(classification trees, Gini splitting), each trained on a **bootstrap sample with ratio
0.3** (each tree sees ~30% of the training set). The training set is a
**stratified 80/20 hold-out split**; reported metric is **Accuracy** on the held-out 20%.

Training is triggered manually: `php artisan ml:train`. The shipped corpus lives in
`database/ml/section_lines.json`; the `--include-resumes` flag additionally harvests
*weak labels* (auto-parsed resume sections) for incremental retraining, and
`--from-fixtures` mines the 246 test fixtures (**43,602 nonblank lines**) with the same
weak-label scheme, capped at `--max-per-class` (default **150**) samples per label to
bound memory and class imbalance.

**Prediction.** For each resume line, the forest returns a per-class probability
distribution via `proba()`; the argmax label is assigned and its probability is kept as
the confidence `score`. A line's prediction is only trusted to switch sections when the
confidence clears `config('ml.default_confidence')` = 0.30 **and** structural gates
(blank-line isolation, paragraph buffering) agree.

```
forecast(h) = argmax_{c ∈ C}  1/T · Σ_{t=1..T}  P_t(y = c | h)
```

### 1.2 K-Means clustering (neighbourhood smoothing)

**Purpose.** Cluster the same resume-line feature vectors into **k = 6** segments
(Rubix `KMeans`, default k-means++ seeding, Euclidean distance). Cluster assignments
drive a **majority-vote smoothing**: a low-confidence line inherits the dominant label
of its cluster if the cluster is **≥ 2 lines** and its majority share is **≥ 0.70**
(`cluster_purity`, `min_cluster_size`). This corrects spurious heading switches.

Given cluster centroids `μ₁..μ₆`:

```
class(x) = argmin_j ‖x − μ_j‖₂
```

Then, for line `x` with confidence below threshold and cluster `j`:

```
label(x) = majority-label(j)   if |j| ≥ 2  and  purity(j) ≥ 0.70
```

### 1.3 Feature vectorization

Each line is converted to a sparse numeric vector:

```
v(line) = [ tf(w₁), …, tf(w_V), s₁, …, s₈ ]
```

- **Vocabulary `V`**: a fixed base vocabulary of section-heading indicators (e.g.
  *experience, skills, education*, 79 terms) merged with **unigrams, bigrams, and
  trigrams** observed ≥ `vocab_min_count` (default 2) times in the corpus, ranked
  **most-frequent-first** and capped at `vocab_cap` (default 220) terms. Ranking by
  frequency rather than alphabetically is what makes the cap meaningful: on the shipped
  corpus it raises mean holdout accuracy from **18.1% to 23.8%** (averaged over
  200/500/800-tree forests), because alphabetical truncation was effectively discarding
  the corpus's most predictive terms. On the 149-line corpus the cap is not binding
  (only 144 terms qualify), so the gain comes from ordering, not size.
- **Term frequency**: raw counts normalized to a 0–1 scale as `min(1, count/3)`;
  multi-word phrase features are matched against the raw lowercased line.
- **8 scalar features**: normalized word count (`min(1, words/8)`), title-case ratio,
  all-caps flag, digit density (`min(3, digits)/3`), trailing-colon flag, trailing-period
  flag, mean word length (`min(1, len/10)`), and line length (`min(1, chars/80)`).

These vectors feed both the Random Forest and the K-Means clusterer, so the two models
an act on identical geometry.

### 1.4 Reproducibility (seeded training)

Rubix's `RandomForest` draws its bootstrap samples via `array_rand()`/`rand()`, and
`KMeans` seeds its centroids through k-means++, which also uses the global PHP RNG. Rubix
2.5 exposes no injectable seed. Left unseeded, **retraining the identical corpus produced
a different model every run**, with holdout accuracy swinging **17.1%–25.7%** and the
artifact checksums changing on each build.

That matters in production because `ml:train` is scheduled: the model would silently
change underneath a running app, and "17.1%" or "25.7%" would be an accident of run order
rather than a property of the data. `ml:train` now calls `mt_srand()`/`srand()` with
`config('ml.train_seed')` (default `20260926`) before fitting, which makes a given corpus
and configuration reproduce **byte-for-byte** — verified over three consecutive builds
(identical classifier and clusterer checksums, identical reported accuracy). Set
`ml.train_seed` to `null` to opt back into unseeded training.

Note that seeding fixes *reproducibility*, not *quality*: it makes the 17.1% stable and
honest rather than a variable 17–26%. Raising the corpus size is the only lever that moved
accuracy materially — harvesting the fixtures lifts it into the **32–36%** band, but only
at sample counts small enough to stay loadable (see §1.5).

### 1.5 Model size ceiling (why the corpus is capped)

The forest is loaded inside a normal web/CLI request sharing the application's **256 MB**
memory ceiling, and Rubix's `Native` serializer materialises the whole model on load. This
creates a hard ceiling on training-set size, measured on the fixture corpus:

| `--max-per-class` | samples | holdout accuracy | artifact | load under 256 MB |
|---|---|---|---|---|
| 60 | ~510 | 31.5% | 552 KB | ok |
| 100 | ~1,000 | 21.8% | 848 KB | ok |
| 150 | 1,360 | 31.9% | 1,270 KB | ok |
| 300 | 2,560 | 35.7% | 2,366 KB | **fails in-app** (ok in isolation) |
| 600 | 4,891 | 37.2% | 4,428 KB | **fails** — memory exhausted |

The `--max-per-class` default of **150** is the largest value measured to stay loadable
in the full application; passing anything higher trades a better holdout score for a model
that fatal-errors on every resume parse. This is a latent trap rather than a hypothetical:
the scheduled `ml:train --include-resumes` job takes this default, and a typical resume
contributes 8–12 lines to `experience`/`skills`, so a database holding ~60–80 parsed
resumes is enough to cross the ceiling on its own. Any corpus change must re-check both
`php artisan ml:status` and the test suite, not just the reported accuracy.

---

## Section 2 — Career recommendation engine

### 2.1 Weighted skill-overlap score

For each career role, the user's declared skills are matched against the role's required
skills. Essential skills carry **70%** weight, preferred **30%**. The two tiers use
different match tests: an **essential** skill counts as matched only when the user's
proficiency meets the role's minimum for it, whereas a **preferred** skill counts as
matched as soon as the user holds it at any level.

```
overlap(role) = (E_matched / E_total)·70  +  (P_matched / P_total)·30
```

where `E` = essential and `P` = preferred sets (each denominator floored at 1). The
`missing` list reports unmet **essential** requirements only — an unmet preferred skill
dilutes the score but is not surfaced as a gap.

`RoleMatchScorer` is the single implementation behind every deterministic skill-match
score, so the subsystems cannot drift apart:

| Caller | "Satisfied" means | Aggregation |
| --- | --- | --- |
| `RoleMatchScorer::overlap()` — role matching, cold-start target pick | user holds the skill (proficiency-gated when essential) | shared |
| `RoleMatchScorer::score()` — job-post matching | user holds the skill (proficiency-gated when essential) | shared |
| `AtsScanner::keywordScore()` — ATS keyword score | the term or an alias appears in the resume text | shared |
| `SkillGapAnalyzer::defaultTargetRole()` — cold-start target pick | via `overlap()` | shared |

The weights live in `config/scoring.php` (`essential_weight`, `preferred_weight`). They
are deliberately config-only rather than an admin setting: changing them shifts every
stored and displayed score, and there is no backfill to re-score historical data.

### 2.2 TF-IDF weighted cosine similarity

A lightweight semantic tier. Each role is a "document" over skill *terms*; the
importance of a term within a role is weighted both by its essential/preferred status
(essential ⇒ ×2) and its inverse document frequency across all roles:

```
idf(t)   = 1 + log( N / df(t) )
role_t  = t in role            ⇒ essential? 2 : 1  ×  min_proficiency  ×  idf(t)
user_t  = user has skill t     ⇒ proficiency_level                        ×  idf(t)
```

```
semantic(role) = cosine(user_vec, role_vec) × 100
              = ( user_vec · role_vec ) / ( ‖user_vec‖ · ‖role_vec‖ ) × 100
```

Pure PHP implementation (`Ml/SkillSimilarity`); no learned embeddings — the `embedding`
columns are a documented, unpopulated seam.

### 2.3 Final ranking with breadth-first path boost

Each role receives a blended score, weighted by admin-tunable defaults
**overlap 60% / semantic 40%**:

```
score(role) = overlap(role)·0.60 + semantic(role)·0.40
```

A **BFS (breadth-first search)** then walks the `career_paths` directed graph starting
from every role with a positive score, up to **2 hops**, and adds a **hop-decayed bonus**
(default **5 points**, `bonus/hops`), capped at 100. This surfaces "next-step" and
adjacent careers without disturbing strong direct matches; seeds themselves are
excluded. Roles are then sorted descending and the **top-N** (default 10) stored.

```
score'(r) = min(100, score(r) + bonus / hops(r))   for hops(r) ∈ {1, 2}
```

A natural-language rationale is generated for the top match — via the LLM provider when
enabled and keyed, otherwise a deterministic template fallback.

---

## Section 3 — ATS resume scoring

ATS score is a weighted composite of four sub-scores (admin-configurable, default
**50 / 15 / 15 / 20**, renormalized to 100).

### 3.1 Keyword match (weight 50%)

Job-requirement skills are counted as present when they (or an alias) occur in the
resume with **word-boundary** matching (case-insensitive, alias-aware term frequency).
Scoring mirrors the overlap scorer:

```
keyword = (H_matched / H_total)·70  +  (S_matched / S_total)·30
```

with `H` = required skills and `S` = preferred/nice-to-have skills.

### 3.2 Structure (15%)

Resume is checked for four core sections — summary, experience, education, skills.
If `f` of the 4 section types are present:

```
structure = (f / 4) × 100
```

### 3.3 Formatting heuristics (15%)

Counts of three resume-quality signals, each normalized:

```
formatting = min(1, bullets/8)·40  +  min(1, actionVerbs/5)·30  +  min(1, quantified/3)·30
```

using bullet-point lines, a 20-item action-verb whitelist, and currency/quantity
tokens (`$`, `%`, 2–4 digit numbers).

### 3.4 Readability (20%)

**Flesch–Kincaid Grade Level** is computed via the standard formula over word count
`W`, sentence count `S`, and syllable count `SYL` (vowel-group heuristic with silent-`e`
adjustment):

```
grade = 0.39·(W/S) + 11.8·(SYL/W) − 15.59
```

which is rescaled to a score (grade 0 ⇒ 50; otherwise clamped to 20–95 as
`100 − max(0, grade − 7)·5`). The ATS subsystem uses `max(20, min(95, …))`; the same
service also powers a standalone readability helper.

### 3.5 Composite

```
overall = round( kw·50/100 + struct·15/100 + fmt·15/100 + read·20/100 )
```

Per-keyword hit/miss details and actionable suggestions (missing keyword phrasings,
missing section labels, over-complex reading level) are persisted alongside the score.

---

## Section 4 — Skill-gap analysis

### 4.1 Gap computation

Against a target role (or job posting), each required skill produces a gap only when
unmet:

```
gap_size = required_level − current_level        (kept when gap_size > 0)
priority = high  if essential / required
         = medium otherwise
```

A `ProgressTracking` row (status `not_started`) is created per gap for the roadmap.

### 4.2 Target-role selection (cold start)

When no explicit target is chosen, the role with the highest positive overlap score
(essential 70 / preferred 30 formula) becomes the target. **If the user has zero
skills**, the system falls back to the first career role alphabetically
(`CareerRole::orderBy('title')->first()`) — audit caveat: this reads as a personalized
pick when it is not.

### 4.3 Readiness score

```
readiness = ( Σ min(current_k, required_k) / Σ required_k ) × 100
```

a signed-off percentage of the required proficiency a user already possesses.

### 4.4 Learning-resource ranking

For each gap, resources teaching that skill are ranked by a **coverage count** (how many
other gap-skill types the resource also teaches, descending), then by **cost ascending**,
then **duration ascending**, keeping the **top 3**:

```
rank(resource) = ( -coverage_count, cost, duration_hours )   →  top 3
```

---

## Section 5 — Deterministic text parsing

Two dictionary-driven extractors complement the ML layer, providing graceful fallback
when models are absent:

1. **Section heading matching.** 29 normalized heading strings (normalized case/colon)
   are mapped onto the 13 canonical section types.
2. **Alias-aware skill extraction.** The skill catalogue and its aliases are matched
   against resume text with **word-boundary regex** (`\b…\b`, case-insensitive), so
   "PHP" and "Preprint" are not confused and variant spellings of a skill resolve to one
   canonical skill entry. Extracted skills auto-register into the user's profile at a
   default proficiency.

---

## Summary table

| Algorithm | Type | Purpose | Implementation |
|---|---|---|---|
| Random Forest (500 trees, ratio 0.3) | Supervised ML | Resume line → 13 section labels | `app/Services/Ml/SectionClassifier.php`, `Console/Commands/MlTrain.php` |
| K-Means (k=6) + majority-vote purity | Unsupervised ML | Heading smoothing / segmentation | `app/Services/Ml/SectionClusterer.php` |
| Bag-of-words n-grams + 8 scalars | Feature engineering | Line vectorization for both models | `app/Services/Ml/ResumeLineVectorizer.php` |
| Weighted skill overlap (70/30) | Deterministic scoring | Role, job & ATS matching | `app/Services/RoleMatchScorer.php`, `config/scoring.php` |
| TF-IDF weighted cosine similarity | Lexical similarity | Semantic role–user similarity | `app/Services/Ml/SkillSimilarity.php` |
| BFS (≤2 hops) + hop-decayed bonus | Graph traversal | Career-path adjacency boost | `app/Services/RecommendationEngine.php` |
| Flesch–Kincaid grade level | Text metric | Resume readability | `app/Services/Readability.php` |
| Heuristic + weighted composite | Rule-based | ATS score (50/15/15/20) | `app/Services/AtsScanner.php` |
| Gap-size + readiness ratio | Arithmetic | Gap assessment | `app/Services/SkillGapAnalyzer.php` |
| Multi-criteria ranking (coverage→cost→duration) | Sorting | Learning-resource recommendation | `app/Services/SkillGapAnalyzer.php` |
| Dictionary + alias word-boundary regex | Text extraction | Resume fallback parsing | `app/Services/ResumeSegmenter.php`, `ResumeParser.php` |
| LLM chat completions (optional) | Generative AI | "Explain with AI" rationale | `app/Services/RecommendationRationalizer.php` |

## References

- `config/ml.php` — model paths and hyper-parameters (k=6, estimators 500, ratio 0.3,
  confidence 0.3, purity 0.7, min cluster size 2, holdout 0.2)
- `config/recommendations.php` — blend weights (60/40), path bonus (5), LLM providers
- `app/Console/Commands/MlTrain.php` — training procedure, stratified holdout, weak-label
  harvesting
- `app/Services/AtsScanner.php`, `app/Services/Readability.php` — ATS sub-scores and
  composite weighting
- `app/Services/SkillGapAnalyzer.php` — gap computation, readiness, resource ranking
- Tests: `tests/Feature/RecommendationEngineTest.php`, `tests/Unit/SkillSimilarityTest.php`,
  `tests/Unit/RoleMatchScorerTest.php`, `tests/Feature/AtsScannerTest.php`