# Appendix A — Core Algorithm Source Listings

**System:** CareerGuide AI (*Ujjwol Bato*) — AI-assisted career guidance with ATS resume
scoring, skill-gap analysis, learning roadmaps, job matching, and career recommendation.

**Stack at time of writing:** Laravel 13.8 (PHP 8.3) · Inertia + Vue 3 · MySQL 8.4 · Rubix/ML

---

## Scope and conventions

This appendix reproduces the algorithmic core of the system. It is deliberately
selective:

| Included | Excluded |
| --- | --- |
| Scoring, blending, and ranking formulas | Framework scaffolding (`<?php`, `namespace`, `use` statements) |
| Graph traversal and staleness logic | Eloquent persistence calls that only store computed values |
| Tunable-weight resolution | Input validation (Form Requests), session/auth plumbing |
| Pure-math helpers (Flesch-Kincaid) | CRUD controllers, Vue components, seeders, admin screens |

**Elision notation.** Removed non-algorithmic lines are marked `// …`. Where a block is
elided, the surrounding logic is unchanged from the source file and remains compilable.
**Source line references** are given per listing as `file.php:START–END` against the
repository at commit `bf826ee`.

The system contains **three scoring tiers** that must not be conflated:

1. **Deterministic tier** — explicit skill-overlap arithmetic (`RoleMatchScorer`).
2. **Semantic tier** — TF-IDF weighted cosine similarity (`SkillSimilarity`).
3. **Generative tier** — an *optional* LLM used only to write human-readable rationale
   text. It never influences any numeric score. It is inert unless both an admin setting
   is enabled **and** a provider key is present, and it falls back to a deterministic
   template on any failure.

All score values below are percentages in the closed interval [0, 100].

---

## A-1. Recommendation engine — tier fusion and career-path propagation

**File:** `app/Services/RecommendationEngine.php` (186 lines) · **Listing:** lines 13–19, 26–95, 104–137, 147–185

This is the system's central contribution. It fuses the deterministic and semantic tiers
into a single match score, then applies a graph-based bonus that surfaces adjacent
("next-step") career roles without inflating already-strong matches.

```php
// app/Services/RecommendationEngine.php
class RecommendationEngine
{
    public function __construct(
        protected RoleMatchScorer $scorer,
        protected SkillSimilarity $similarity,
        protected RecommendationRationalizer $rationalizer,
    ) {}

    public function generate(Profile $profile): Collection
    {
        CareerRecommendation::where('profile_id', $profile->id)->delete();

        $userSkills = $profile->userSkills()->with('skill')->get();

        if ($userSkills->isEmpty()) {
            return collect();
        }

        $roles = CareerRole::with('requiredSkills')->get();

        if ($roles->isEmpty()) {
            return collect();
        }

        $userSkillMap = $userSkills->keyBy('skill_id');

        $weights = (array) Settings::get('recommendation_weights', config('recommendations.weights'));
        $weightOverlap = (float) ($weights['overlap'] ?? 60) / 100;
        $weightSemantic = (float) ($weights['semantic'] ?? 40) / 100;

        $scored = [];
        $corpus = $this->similarity->corpus($roles);

        foreach ($roles as $role) {
            $overlap = $this->scorer->overlap($userSkillMap, $role);
            $semantic = $this->similarity->score($userSkillMap, $role, $roles, $corpus);

            $scored[$role->id] = [
                'role' => $role,
                'overlap' => $overlap,
                'semantic' => $semantic,
                'score' => $overlap['score'] * $weightOverlap + $semantic * $weightSemantic,
            ];
        }

        $this->applyPathBoost($scored);

        uasort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $topCount = max((int) Settings::get('top_matches', config('settings.definitions.top_matches.default', 10)), 1);
        $topIds = array_slice(array_keys($scored), 0, $topCount);

        $created = collect();

        foreach ($topIds as $index => $roleId) {
            $row = $scored[$roleId];

            // Only the single best match is sent to the LLM; all other rows are
            // rendered by the deterministic template, bounding API cost to 1 call
            // per generation regardless of the top-N setting.
            if ($index === 0) {
                $rationale = $this->rationalizer->for($profile, $row['role'], $row['overlap']);
            } else {
                $rationale = [
                    'text' => $this->rationalizer->template($profile, $row['role'], $row['overlap']),
                    'source' => 'template',
                ];
            }

            $created->push(CareerRecommendation::create([
                // … profile_id, career_role_id, match_score, rationale,
                //     rationale_source, generated_at …
            ]));
        }

        return $created;
    }
```

### Career-path bonus

A small bonus is granted to roles reachable through the `career_paths` directed graph,
decaying with hop distance so that one-step transitions are favoured over two-step
ones. Roles that already score above zero are excluded as seeds, so the bonus can only
*promote* weakly-scoring adjacent roles — it can never reorder a user's genuine top
matches.

```php
    protected function applyPathBoost(array &$scored): void
    {
        $bonus = (float) Settings::get('recommendation_path_bonus', config('recommendations.path_bonus', 5));

        if ($bonus <= 0) {
            return;
        }

        $adjacency = [];

        foreach (CareerPath::query()->get(['from_role_id', 'to_role_id']) as $path) {
            $adjacency[$path->from_role_id][] = $path->to_role_id;
        }

        if ($adjacency === []) {
            return;
        }

        $seeds = [];

        foreach ($scored as $roleId => $row) {
            if ($row['score'] > 0) {
                $seeds[] = $roleId;
            }
        }

        foreach ($this->reachable($seeds, $adjacency) as $roleId => $hops) {
            if (! isset($scored[$roleId])) {
                continue;
            }

            $scored[$roleId]['score'] = min(100, $scored[$roleId]['score'] + $bonus / $hops);
        }
    }
```

### Bounded breadth-first reachability

Standard BFS over the career-path adjacency list, with depth truncated at two hops and
per-seed visited sets. Seeds are pre-marked as visited, which yields a shortest-hop
distance map *excluding* the seeds themselves.

```php
    protected function reachable(array $seeds, array $adjacency): array
    {
        if ($seeds === []) {
            return [];
        }

        $reachable = [];
        $maxHops = 2;

        foreach ($seeds as $seed) {
            $queue = [[$seed, 0]];
            $visited = [$seed => true];

            while ($queue !== []) {
                [$current, $hops] = array_shift($queue);

                if ($hops >= $maxHops) {
                    continue;
                }

                foreach ($adjacency[$current] ?? [] as $next) {
                    if (isset($visited[$next])) {
                        continue;
                    }

                    $visited[$next] = true;
                    $distance = $hops + 1;

                    if (! isset($reachable[$next]) || $distance < $reachable[$next]) {
                        $reachable[$next] = $distance;
                    }

                    $queue[] = [$next, $distance];
                }
            }
        }

        return $reachable;
    }
}
```

**Resulting rank function.** For role *r*:

```
              ⎧ 0.60 · overlap(r) + 0.40 · semantic(r)          if r scores 0 or r is a seed
score(r)  =  ⎨
              ⎩ min(100, score(r) + 5 / h(r))                    h(r) = min hop distance, 1 ≤ h(r) ≤ 2
```

Default weights: `overlap = 60`, `semantic = 40`, `path_bonus = 5`. The `min(100, ·)`
clamp guarantees the bonus can never push a score out of the reportable range.

---

## A-2. Shared deterministic skill-overlap scorer

**File:** `app/Services/RoleMatchScorer.php` (170 lines) · **Listing:** lines 21–24, 40–67, 82–138

One scoring routine serves three subsystems: career-role matching, job-posting matching,
and ATS keyword matching. Centralising it is what prevents the three from drifting apart
over time, since they share a single weight source and a single formula.

```php
// app/Services/RoleMatchScorer.php
class RoleMatchScorer
{
    public function overlap($userSkillMap, CareerRole $role): array
    {
        return $this->score($userSkillMap, $role->requiredSkills);
    }

    public function score($userSkillMap, $requirements): array
    {
        if ($requirements->isEmpty()) {
            return ['score' => 0, 'matched' => [], 'missing' => []];
        }

        $satisfied = [];

        foreach ($requirements as $skill) {
            $user = $userSkillMap->get($this->skillId($skill));

            if (! $user) {
                continue;
            }

            // Essential skills must be met at the required proficiency;
            // preferred skills only need to be present at all.
            $isSatisfied = $this->importance($skill) === 'essential'
                ? $user->proficiency_level >= $this->minProficiency($skill)
                : true;

            if ($isSatisfied) {
                $satisfied[$this->skillId($skill)] = true;
            }
        }

        return $this->aggregate($requirements, $satisfied);
    }
```

The aggregation step is separated from the satisfaction test so that a caller with a
different notion of "satisfied" — the ATS scanner, which tests literal text presence
rather than proficiency — can reuse the identical weighting.

```php
    public function aggregate($requirements, array $satisfied): array
    {
        if ($requirements->isEmpty()) {
            return ['score' => 0, 'matched' => [], 'missing' => []];
        }

        $essential = $requirements->filter(fn ($item) => $this->importance($item) === 'essential');
        $preferred = $requirements->filter(fn ($item) => $this->importance($item) === 'preferred');

        $weights = $this->weights();
        $essentialTotal = max($essential->count(), 1);
        $preferredTotal = max($preferred->count(), 1);

        $essentialMatched = 0;
        $preferredMatched = 0;
        $matched = [];
        $missing = [];

        foreach ($essential as $skill) {
            if (isset($satisfied[$this->skillId($skill)])) {
                $essentialMatched++;
                $matched[] = $this->skillId($skill);
            } else {
                $missing[] = $this->skillId($skill);
            }
        }

        // Preferred skills only require the skill to be present at all.
        foreach ($preferred as $skill) {
            if (isset($satisfied[$this->skillId($skill)])) {
                $preferredMatched++;
                $matched[] = $this->skillId($skill);
            }
        }

        $score = (int) round(
            ($essentialMatched / $essentialTotal) * $weights['essential']
            + ($preferredMatched / $preferredTotal) * $weights['preferred']
        );

        return [
            'score' => $score,
            'matched' => array_values(array_unique($matched)),
            'missing' => array_values(array_unique($missing)),
        ];
    }

    public function weights(): array
    {
        return [
            'essential' => (int) config('scoring.essential_weight', 70),
            'preferred' => (int) config('scoring.preferred_weight', 30),
        ];
    }
}
```

**Formula.**

```
                     m_e   w_e          m_p   w_p
overlap(r)  =  round(───── ·  +  ───── ·  ) × 100
                    |E|              |P|
```

where *E* and *P* are the essential and preferred requirement sets, *m_e* / *m_p* the
counts satisfied in each, and *(w_e, w_p) = (70, 30)* by default. `max(·, 1)` guards
division by zero for either empty set, so a role with only preferred requirements is
scored on a 0–30 scale rather than being treated as unmatchable.

**Polymorphic requirement-shape adapters** (lines 144–169). Career roles and job postings
store their requirement metadata in structurally different places; collapsing both onto
one shape here is what allows a single formula to serve both.

```php
    // Career roles use essential/preferred; job postings use required/preferred.
    private function importance(mixed $item): string
    {
        $raw = isset($item->pivot) ? $item->pivot->importance : $item->importance;

        return $raw === 'essential' || $raw === 'required' ? 'essential' : 'preferred';
    }

    // JobRequiredSkill pivot rows carry their own UUID PK, which differs from
    // skill_id; conflating the two silently zeroes every score.
    private function skillId(mixed $item): string
    {
        return isset($item->pivot) ? (string) $item->id : (string) $item->skill_id;
    }

    private function minProficiency(mixed $item): int
    {
        $raw = isset($item->pivot) ? $item->pivot->min_proficiency : $item->min_proficiency;

        return (int) $raw;
    }
```

The 70/30 weights are intentionally **config-only**, not exposed as an admin setting
(`config/scoring.php:14-18`): changing them would retroactively shift every previously
stored score with no re-scoring backfill available.

---

## A-3. Semantic tier — TF-IDF weighted cosine similarity

**File:** `app/Services/Ml/SkillSimilarity.php` (153 lines) · **Listing:** lines 25–57, 65–77, 85–118, 125–153

The semantic tier measures similarity between the user's proficiency vector and a role's
requirement vector. It is implemented in pure PHP with no model artifact, no embedding
service, and no network call — which makes it fully deterministic and unit-testable.
Where no reliable signal exists it returns 0, matching the behaviour of an ML model whose
embedding is unavailable.

```php
// app/Services/Ml/SkillSimilarity.php
class SkillSimilarity
{
    public function score($userSkillMap, CareerRole $role, ?Collection $roleDocs = null, ?array $corpus = null): float
    {
        $required = $role->requiredSkills;

        if ($required->isEmpty()) {
            return 0.0;
        }

        $corpus ??= $this->corpus($roleDocs ?? collect([$role]));
        $df = $corpus['df'];
        $documentCount = $corpus['count'];

        $roleVector = [];
        $userVector = [];

        foreach ($required as $skill) {
            $term = $this->dimension($skill->name);
            $occurrences = max($df[$term] ?? 1, 1);
            $idf = 1 + log(max($documentCount, 1) / $occurrences);

            $minProficiency = (int) $skill->pivot->min_proficiency;
            $importance = $skill->pivot->importance === 'essential' ? 2 : 1;
            $roleTermWeight = $importance * $minProficiency;
            $roleVector[$term] = max($roleVector[$term] ?? 0.0, $roleTermWeight * $idf);

            $user = $userSkillMap->get($skill->id);
            $userProficiency = $user ? (int) $user->proficiency_level : 0;
            $userTermWeight = $userProficiency >= $minProficiency ? $userProficiency : 0;
            $userVector[$term] = max($userVector[$term] ?? 0.0, $userTermWeight * $idf);
        }

        return round($this->cosine($userVector, $roleVector) * 100, 2);
    }
```

Three design decisions are worth stating explicitly:

- **Term weighting.** A requirement's weight is `importance × min_proficiency`, with
  essential skills counted twice. Rare skills therefore dominate the cosine, which is the
  intended behaviour: differentiating on *Kubernetes* should matter more than on *Excel*.
- **Symmetric proficiency gating.** The user's term contributes **only** when their
  proficiency meets the role's minimum, mirroring `RoleMatchScorer`. Without this the two
  tiers would disagree about what "has this skill" means and the blend would be
  incoherent.
- **Corpus reuse.** Document frequencies are computed once per generation, not once per
  role. With *N* roles this reduces IDF computation from *O(N²)* to *O(N)* skill
  comparisons.

```php
    public function corpus(iterable $roles): array
    {
        $df = $this->documentFrequencies($roles);
        $count = 0;

        foreach ($roles as $role) {
            if ($role instanceof CareerRole) {
                $count++;
            }
        }

        return ['df' => $df, 'count' => max($count, 1)];
    }

    public function cosine(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $key => $value) {
            $value = (float) $value;
            if ($value == 0) {
                continue;
            }
            $normA += $value * $value;

            if (isset($b[$key]) && (float) $b[$key] != 0) {
                $dot += $value * (float) $b[$key];
            }
        }

        foreach ($b as $value) {
            $value = (float) $value;
            if ($value == 0) {
                continue;
            }
            $normB += $value * $value;
        }

        $denominator = sqrt($normA) * sqrt($normB);

        if ($denominator == 0.0) {
            return 0.0;
        }

        return (float) ($dot / $denominator);
    }

    public function documentFrequencies(iterable $roles): array
    {
        $df = [];

        foreach ($roles as $role) {
            if (! $role instanceof CareerRole) {
                continue;
            }

            $seen = [];

            foreach ($role->requiredSkills as $skill) {
                $term = $this->dimension($skill->name);

                if (! isset($seen[$term])) {
                    $seen[$term] = true;
                    $df[$term] = ($df[$term] ?? 0) + 1;
                }
            }
        }

        return $df;
    }

    protected function dimension(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? $name));
    }
}
```

**Formula.** With *V* the term set of a role's requirements, *idf(t) = 1 + ln(N / df(t))*,
role vector **R** and user vector **U** as built above:

```
                              Σ  R(t)·U(t)
semantic(r)  =  round( ──────────────────── × 100, 2 )
                        ‖R‖₂ · ‖U‖₂
```

Both IDF terms use the smoothed form `1 + ln(...)`, so a term appearing in every role
contributes `idf = 1` rather than zero, preventing total signal loss on ubiquitous skills.

---

## A-4. Skill-gap analysis, readiness, and roadmap ranking

**File:** `app/Services/SkillGapAnalyzer.php` (188 lines) · **Listing:** lines 15–86, 92–101, 121–163, 165–187

Converts a target role's requirements into per-skill gap items, a proficiency-weighted
readiness score, and a ranked set of learning resources. Also implements a staleness
fingerprint so that a roadmap built against an old skill profile can be detected as
outdated rather than silently displayed as current.

```php
// app/Services/SkillGapAnalyzer.php
class SkillGapAnalyzer
{
    public function run(
        Profile $profile,
        $userSkillMap,
        ?CareerRole $targetRole = null,
    ): ?GapAssessment {
        $targetRole = $targetRole ?? $this->defaultTargetRole($profile, $userSkillMap);
        if (! $targetRole) {
            return null;
        }

        $requirements = $targetRole->requiredSkills()
            ->get()
            ->map(fn ($skill) => [
                'skill_id' => $skill->id,
                'skill_name' => $skill->name,
                'required_level' => $skill->pivot->min_proficiency,
                'importance' => $skill->pivot->importance,
            ]);

        if ($requirements->isEmpty()) {
            return null;
        }

        $assessment = GapAssessment::create([
            // … profile_id, target_role_id, overall_readiness_score …
        ]);

        $this->purgeStaleProgress($profile, $assessment);   // see note below

        $gapSkills = collect();

        foreach ($requirements as $requirement) {
            $user = $userSkillMap->get($requirement['skill_id']);
            $currentLevel = $user ? (int) $user->proficiency_level : 0;
            $requiredLevel = (int) $requirement['required_level'];
            $gapSize = $requiredLevel - $currentLevel;

            if ($gapSize <= 0) {
                continue;
            }

            $item = GapAssessmentItem::create([
                // … gap_assessment_id, skill_id, required_level, current_level,
                //     gap_size …
                'priority' => $requirement['importance'] === 'essential' ? 'high' : 'medium',
            ]);

            ProgressTracking::create([
                // … profile_id, gap_assessment_item_id, status = 'not_started' …
            ]);

            $gapSkills->push($requirement['skill_id']);
        }

        $assessment->update([
            'overall_readiness_score' => $this->readinessScore($requirements, $userSkillMap),
            'skills_fingerprint' => self::fingerprint($userSkillMap),
        ]);

        if ($gapSkills->isNotEmpty()) {
            $this->rankRecommendations($assessment, $gapSkills);
        }

        return $assessment;
    }
```

> **Elided method — `purgeStaleProgress()`** (lines 107–119). Deletes `ProgressTracking`
> rows belonging to *prior* assessments of the same profile, so re-running an assessment
> cannot orphan stale roadmap progress. Omitted here as pure persistence, but it is
> required for roadmap correctness and is covered by `tests/Feature/AuditHighFixesTest.php`.

### Staleness fingerprint

```php
    /**
     * Signature of the user's current skill map so the roadmap can detect when
     * an assessment no longer reflects the profile.
     */
    public static function fingerprint($userSkillMap): string
    {
        $pairs = collect($userSkillMap)
            ->map(fn ($us) => $us->skill_id.':'.(int) $us->proficiency_level)
            ->sort()
            ->values()
            ->implode('|');

        return md5($pairs);
    }
```

Skill IDs are sorted before hashing so the fingerprint is order-independent: adding,
removing, or changing the proficiency of any skill invalidates the stored roadmap, while
mere reordering does not.

### Cold-start target selection

When no target role is supplied, the highest-scoring role is chosen. Returning `null` for
a zero-skill user is deliberate — an earlier implementation fell back to the
alphabetically-first role, which produced meaningless roadmaps for new users.

```php
    private function defaultTargetRole($profile, $userSkillMap): ?CareerRole
    {
        $roles = CareerRole::with('requiredSkills')->get();
        $scorer = app(RoleMatchScorer::class);
        $best = null;
        $bestScore = 0;

        foreach ($roles as $role) {
            if ($role->requiredSkills->isEmpty()) {
                continue;
            }

            $score = $scorer->overlap($userSkillMap, $role)['score'];

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $role;
            }
        }

        return $best;
    }
```

### Proficiency-weighted readiness

Readiness is a *level ratio*, not a match ratio: each required skill contributes up to its
required level, capped at the requirement so that over-qualification in one skill cannot
mask a deficit in another.

```php
    private function readinessScore($requirements, $userSkillMap): float
    {
        if ($requirements->isEmpty()) {
            return 0;
        }

        $numerator = 0;
        $denominator = 0;

        foreach ($requirements as $requirement) {
            $required = (int) $requirement['required_level'];
            $user = $userSkillMap->get($requirement['skill_id']);
            $current = min($user ? (int) $user->proficiency_level : 0, $required);

            $numerator += $current;
            $denominator += $required;
        }

        return round(($numerator / max($denominator, 1)) * 100, 2);
    }
```

**Formula.**

```
                     Σ  min(pᵢ, rᵢ)
readiness  =  round( ─────────────── × 100, 2 )
                    Σ  rᵢ
```

where *pᵢ* is the user's proficiency for required skill *i* and *rᵢ* its required level.

### Learning-resource ranking

Resources are ranked by **set coverage**, not by relevance to the individual skill: a
course that also closes two other gaps is preferred over a narrowly-targeted one, since
the user must clear every gap regardless.

```php
    private function rankRecommendations(GapAssessment $assessment, $gapSkillIds): void
    {
        $gapSkillList = $gapSkillIds->values()->all();

        foreach ($assessment->items as $item) {
            $candidates = LearningResource::query()
                ->whereHas('skills', fn ($q) => $q->whereKey($item->skill_id))
                ->withCount(['skills' => fn ($q) => $q->whereIn('skills.id', $gapSkillList)])
                ->orderByDesc('skills_count')
                ->orderBy('cost')
                ->orderBy('duration_hours')
                ->take(3)
                ->get();

            foreach ($candidates as $index => $resource) {
                GapRecommendation::create([
                    'gap_assessment_item_id' => $item->id,
                    'learning_resource_id' => $resource->id,
                    'rank' => $index + 1,
                ]);
            }
        }
    }
}
```

The `withCount` sub-query restricts coverage counting to *this user's* gap skills, so a
resource covering fourteen skills the user does not need does not outrank a cheaper
three-skill course. Ties break on cost, then duration — the lexicographic order of
`orderByDesc` / `orderBy` calls in Laravel.

---

## A-5. ATS resume scanner — four-component weighted score

**File:** `app/Services/AtsScanner.php` (281 lines) · **Listing:** lines 21–66, 68–113, 115–198

Produces a resume-compatibility score against a specific job posting. The overall score is
a weighted sum of four independent sub-scores: keyword match, section structure, formatting,
and readability.

```php
// app/Services/AtsScanner.php
class AtsScanner
{
    public function scan(Resume $resume, JobPosting $jobPosting): AtsScan
    {
        $resumeText = $resume->raw_text ?? '';
        $jdText = $jobPosting->raw_description ?? '';

        $resume->loadMissing('sections', 'profile');

        [$keywordScore, $keywords] = $this->keywordScore($resumeText, $jdText, $jobPosting);
        $structureScore = $this->structureScore($resume);
        $formatting = $this->formattingBreakdown($resumeText);
        $formattingScore = $this->formattingScore($formatting);
        $grade = app(Readability::class)->gradeLevel($resumeText);
        $readabilityScore = $this->readabilityScore($grade);
        $weights = $this->weights();

        $overallScore = round(
            $keywordScore * $weights['keyword'] / 100
                + $structureScore * $weights['structure'] / 100
                + $formattingScore * $weights['formatting'] / 100
                + $readabilityScore * $weights['readability'] / 100,
        );

        $scan = AtsScan::create([
            'resume_id' => $resume->id,
            'job_posting_id' => $jobPosting->id,
            'overall_score' => $overallScore,
            'keyword_match_score' => $keywordScore,
            'formatting_score' => $formattingScore,
            'structure_score' => $structureScore,
            'readability_score' => $readabilityScore,
        ]);

        foreach ($keywords as $keyword) {
            AtsMatchedKeyword::create([
                // … ats_scan_id, keyword, matched,
                //     frequency_in_resume, frequency_in_jd …
            ]);
        }

        $this->writeDetails($scan, $keywords, $resume, $grade, $formatting);   // see note

        return $scan->load(['matchedKeywords', 'details']);
    }
```

> **Elided method — `writeDetails()`** (lines 200–280). Converts the four sub-scores into
> per-issue `AtsScanDetail` rows (category, detail, severity, suggestion). Omitted as
> presentational string construction; it derives no score.

### Keyword sub-score with alias expansion

A requirement counts as matched when either its canonical name **or any of its recorded
aliases** appears in the resume text. Aliases must also be counted against the job
description, since a posting may use the abbreviation (*"K8s"*) rather than the canonical
name (*"Kubernetes"*).

```php
    private function keywordScore(string $resumeText, string $jdText, JobPosting $jobPosting): array
    {
        $requirements = $jobPosting->requiredSkills()->with('skill.aliases')->get();
        $keywords = [];
        $satisfied = [];

        foreach ($requirements as $req) {
            $skill = $req->skill;
            if (! $skill) {
                continue;
            }

            $needle = strtolower($skill->name);
            $freqResume = $this->countOccurrences($resumeText, $needle);
            $freqJd = $this->countOccurrences($jdText, $needle);
            $matched = $freqResume > 0;

            foreach ($skill->aliases as $alias) {
                $aliasNeedle = strtolower($alias->alias);
                $aliasFreq = $this->countOccurrences($resumeText, $aliasNeedle);
                $freqResume += $aliasFreq;
                $matched = $matched || $aliasFreq > 0;

                // The JD may use the alias ("K8s") rather than the canonical
                // name ("Kubernetes"), so count it here too.
                $freqJd += $this->countOccurrences($jdText, $aliasNeedle);
            }

            if ($matched) {
                $satisfied[$skill->id] = true;
            }

            $keywords[] = [
                'keyword' => $skill->name,
                'matched' => $matched,
                'frequency_in_resume' => $freqResume,
                'frequency_in_jd' => $freqJd,
            ];
        }

        // Shares the essential/preferred weighting with role and job matching;
        // only the "is it satisfied" test differs (resume text, not proficiency).
        $score = app(RoleMatchScorer::class)->aggregate($requirements, $satisfied)['score'];

        return [$score, $keywords];
    }
```

This is the concrete payoff of the shared-scorer design in A-2. The ATS score is *not* a
separate formula — it is `RoleMatchScorer::aggregate()` with a different satisfaction
predicate, so the essential/preferred weighting is provably identical across all three
subsystems.

### Structure, formatting, and readability sub-scores

```php
    private function structureScore(Resume $resume): int
    {
        $expected = ['summary', 'experience', 'education', 'skills'];
        $present = $resume->sections->pluck('section_type')->unique();

        $found = collect($expected)->filter(fn ($type) => $present->contains($type))->count();

        return (int) round(($found / count($expected)) * 100);
    }

    private function formattingBreakdown(string $resumeText): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $resumeText) ?: [];

        $bullets = count(array_filter($lines, fn ($line) => preg_match('/^\s*[-•*▪]\s+/', $line)));

        $lower = strtolower($resumeText);
        $verbHits = 0;
        foreach ($this->actionVerbs as $verb) {
            $verbHits += substr_count($lower, $verb);
        }

        preg_match_all('/[$%]|\b\d{2,4}\b/', $resumeText, $matches);
        $quantified = count($matches[0]);

        return [
            'bullets' => $bullets,
            'verb_hits' => $verbHits,
            'quantified' => $quantified,
        ];
    }

    private function formattingScore(array $formatting): int
    {
        $bullets = $formatting['bullets'];
        $verbHits = $formatting['verb_hits'];
        $quantified = $formatting['quantified'];

        return (int) round((min(1, $bullets / 8) * 40) + (min(1, $verbHits / 5) * 30) + (min(1, $quantified / 3) * 30));
    }

    private function readabilityScore(float $grade): int
    {
        if ($grade === 0) {
            return 50;   // neutral default when text is too short to grade
        }

        return (int) round(max(20, min(95, 100 - max(0, $grade - 7) * 5)));
    }
```

Formatting uses a **saturating** score: each of the three signals saturates at a
recommended threshold (8 bullets, 5 action verbs, 3 quantified achievements) and
contributes nothing thereafter. This prevents a single verbose signal from dominating.
Readability is inverted grade level, penalised 5 points per grade above 7, clamped to
[20, 95] so that a difficult-but-legitimate technical resume is not scored near zero.

### Normalised, admin-tunable weights

```php
    public function weights(): array
    {
        $default = [
            'keyword' => 50,
            'structure' => 15,
            'formatting' => 15,
            'readability' => 20,
        ];

        $weights = Settings::get('ats_weights', $default);

        $weights = is_array($weights)
            ? array_merge($default, $weights)
            : $default;

        $total = array_sum($weights);

        if ($total !== 100) {
            $weights = array_map(fn ($w) => (int) round($w / max($total, 1) * 100), $weights);
        }

        return $weights;
    }

    private function countOccurrences(string $haystack, string $needle): int
    {
        if ($needle === '') {
            return 0;
        }

        preg_match_all('/\b'.preg_quote($needle, '/').'\b/i', $haystack, $matches);

        return count($matches[0]);
    }
}
```

`array_merge` with defaults makes this forward-compatible: if an administrator saves a
partial weight group, missing keys inherit defaults rather than becoming undefined. The
renormalisation branch guarantees the four weights always sum to 100 regardless of what
was stored, so the weighted sum in `scan()` cannot silently under- or over-score.

Occurrence counting is **word-boundary anchored** (`\b…\b`) and case-insensitive, which
prevents false positives such as `R` matching inside `Ruby` — a defect that would
otherwise inflate the keyword sub-score.

---

## A-6 (supplementary). Flesch-Kincaid readability

**File:** `app/Services/Readability.php` (48 lines) · **Listing:** lines 7–47

Included because it is the only purely closed-form model in the system and is fully
reproducible. The readability sub-score in A-5 depends on it.

```php
// app/Services/Readability.php
class Readability
{
    public function gradeLevel(string $text): float
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $wordCount = count($words);

        if ($wordCount < 10) {
            return 0;
        }

        // sentenceCount() floors at 1, so both divisors are non-zero here.
        $sentences = $this->sentenceCount($text);
        $syllables = array_sum(array_map(fn ($word) => $this->syllableCount($word), $words));

        // Flesch-Kincaid Grade Level
        $score = 0.39 * ($wordCount / $sentences) + 11.8 * ($syllables / $wordCount) - 15.59;

        return max(0, round($score, 1));
    }

    private function sentenceCount(string $text): int
    {
        preg_match_all('/[.!?]+(\s|$)/', $text, $matches);

        return count($matches[0]) ?: 1;
    }

    private function syllableCount(string $word): int
    {
        $word = strtolower(preg_replace('/[^a-z]/', '', $word));
        if ($word === '') {
            return 0;
        }

        $count = preg_match_all('/[aeiouy]+/', $word);

        if (preg_match('/e$/', $word)) {
            $count--;   // silent trailing "e"
        }

        return max(1, $count);
    }
}
```

**Formula.**

```
                 0.39 · (words / sentences)
FKGL   =   max(0, ─────────────────────────────)
                 + 11.8 · (syllables / words) − 15.59
```

Syllable counting is a heuristic vowel-group count with trailing-`e` suppression and a
`max(1, ·)` floor. It is approximate for irregular words; this is acceptable because the
result feeds a 5-points-per-grade normalisation, not a publication-grade measurement. The
`wordCount < 10` guard returns 0 rather than a fabricated grade, which A-5 maps to a
neutral 50.

---

## Appendix B. Tunable parameter register

Every score-producing constant in the system is declared in exactly one place. This
register is the complete set.

| Parameter | Source | Default | Runtime-tunable | Affects |
| --- | --- | --- | --- | --- |
| `essential_weight` | `config/scoring.php:23` | 70 | No (config only) | Role match, job match, ATS keyword |
| `preferred_weight` | `config/scoring.php:25` | 30 | No (config only) | Role match, job match, ATS keyword |
| `weights.overlap` | `config/recommendations.php:16` | 60 | Yes — Settings → Scoring | Tier blend |
| `weights.semantic` | `config/recommendations.php:17` | 40 | Yes — Settings → Scoring | Tier blend |
| `path_bonus` | `config/recommendations.php:30` | 5 | Yes — Settings → Scoring | Career-path promotion |
| `top_matches` | `config/settings.php:55` | 10 (1–50) | Yes — Settings → Limits | Result cardinality |
| `ats_weights` (4 keys) | `config/settings.php:38` | 50/15/15/20 | Yes — Settings → Scoring | ATS overall score |
| `rationale_default` | `config/recommendations.php:47` | `disabled` | Yes — Settings → ML | LLM activation gate |
| `timeout` | `config/recommendations.php:72` | 60 s | No (env) | LLM call only |
| `max_tokens` | `config/recommendations.php:73` | 1024 | No (env) | LLM call only |

`ats_weights` is a `percent_group` setting and is renormalised at read time (A-5), so the
four values always sum to 100. The two `scoring.php` weights are deliberately excluded
from the admin UI: altering them would retroactively invalidate every stored
`match_score` with no backfill, so they require a redeploy.

---

## Appendix C. Algorithm summary

**Career recommendation** (`app/Services/RecommendationEngine.php`)

```
1.  Load user skill map; abort if empty (cold start → prompt onboarding).
2.  Load all career roles with required skills; abort if none.
3.  Build TF-IDF corpus statistics once (DF + N) over all roles.
4.  For each role r:
        overlap(r)  = RoleMatchScorer::score(user, r.requiredSkills)      [A-2]
        semantic(r) = SkillSimilarity::score(user, r, corpus)             [A-3]
        score(r)    = 0.60·overlap(r) + 0.40·semantic(r)                 [A-1]
5.  Add hop-decayed path bonus to non-seed roles (BFS, depth ≤ 2), clamp to 100.
6.  Sort descending; keep top N.
7.  LLM rationale for rank 1 only; deterministic template for the remainder.
8.  Replace the stored snapshot (no history retained).
```

**Skill gap analysis** (`app/Services/SkillGapAnalyzer.php`)

```
1.  Resolve target role: explicit, else highest-overlap role; null if none.
2.  Map requirements → (skill_id, required_level, importance).
3.  Purge ProgressTracking rows from prior assessments of this profile.
4.  For each requirement: gap = required − current; skip if ≤ 0.
        priority = essential ? high : medium
5.  readiness = Σ min(current, required) / Σ required                    [A-4]
6.  Store fingerprint = md5(sorted "skill_id:proficiency" pairs).
7.  Rank up to 3 learning resources per gap by gap-set coverage, then cost, then duration.
```

**ATS scan** (`app/Services/AtsScanner.php`)

```
1.  keyword     = RoleMatchScorer::aggregate(job.requiredSkills, matched-by-text)   [A-2]
2.  structure   = 100 × |{summary, experience, education, skills} present| / 4
3.  formatting  = 40·min(1, bullets/8) + 30·min(1, verbs/5) + 30·min(1, numbers/3)
4.  readability = clamp(100 − 5·max(0, FKGL − 7), 20, 95);  50 if ungradable   [A-6]
5.  overall     = Σ component × weight / 100,  weights renormalised to 100.
6.  Persist scan, matched keywords, and per-issue remediation details.
```

---

## Appendix D. Verification

Every algorithm in A-1 through A-6 is covered by automated tests. The deterministic and
semantic tiers are unit-tested with no network access and no trained model, so they are
reproducible offline. The generative tier is tested against a faked HTTP client.

The table below cites, for each listing, the specific test methods that assert the
behaviours described above — not merely the file that contains them.

| Listing | Asserting test method | File |
| --- | --- | --- |
| A-1 top-N, LLM only for rank 1 | `test_only_the_top_recommendation_gets_llm_rationale_when_enabled` | `tests/Unit/RecommendationEngineTest.php:69` |
| A-1 cold start / snapshot replace | `test_returns_empty_and_wipes_rows_when_profile_has_no_skills`; `test_regenerating_replaces_previous_rows` | `RecommendationEngineTest.php:93,104` |
| A-2 essential vs. preferred | `test_essential_requires_minimum_proficiency_but_preferred_only_requires_presence` | `tests/Unit/RoleMatchScorerTest.php:38` |
| A-2 70/30 weights | `test_weights_come_from_config_and_default_to_seventy_thirty` | `RoleMatchScorerTest.php:61` |
| A-2 pivot-vs-row id resolution | `test_job_posting_requirements_resolve_skill_id_not_the_pivot_row_id` | `RoleMatchScorerTest.php:93` |
| A-2 shared with ATS | `test_aggregate_shares_the_weighting_for_callers_with_their_own_match_rule` | `RoleMatchScorerTest.php:135` |
| A-3 zero-signal behaviour | `test_returns_zero_for_a_role_with_no_requirements` | `tests/Unit/SkillSimilarityTest.php:22` |
| A-3 cosine extremity | `test_full_similarity_for_exact_match`; `test_scoring_is_higher_for_more_similar_roles` | `SkillSimilarityTest.php:29,42` |
| A-3 proficiency gating | `test_proficiency_below_the_role_minimum_does_not_contribute` | `SkillSimilarityTest.php:63` |
| A-3 corpus reuse is sound | `test_precomputed_corpus_matches_a_freshly_computed_one` | `SkillSimilarityTest.php:93` |
| A-1 / §Scope LLM is inert by default | `test_uses_template_when_setting_is_disabled_even_with_a_key`; `…_when_no_key_is_configured_even_if_enabled`; `…_when_no_endpoint_is_configured` | `tests/Unit/RecommendationRationalizerTest.php:53,68,82` |
| §Scope silent fallback | `test_silently_falls_back_to_template_when_provider_fails` | `RecommendationRationalizerTest.php:215` |
| A-5 alias expansion (both directions) | `test_job_description_using_an_alias_is_counted_as_a_keyword_hit`; `test_alias_free_keyword_frequency_is_unchanged` | `tests/Feature/AtsScannerTest.php:58,91` |
| A-4 fingerprint staleness | `test_roadmap_flags_stale_assessment_when_skills_change` | `tests/Feature/AuditHighFixesTest.php:110` |
| A-4 target-role resolution | `test_assessment_requires_skills_or_target_role`; `test_assessment_rerun_preserves_target_role` | `AuditHighFixesTest.php:67,81` |
| A-5, A-6 resume ingestion | (TXT / PDF / DOCX fixtures) | `tests/Feature/Resume/ResumeParserTest.php`, `ResumeUploadTest.php` |

`test_precomputed_corpus_matches_a_freshly_computed_one` deserves a note: it is what
justifies the corpus-reuse optimisation in A-3, proving the cached DF statistics are
equivalent to recomputing them per role.

`tests/Feature/AuditHighFixesTest.php` is the regression suite for the eight
high-severity issues identified during internal audit. Two behaviours documented above
exist directly because of failures recorded there: the cold-start target-role selection
in A-4, and the cache-lock handling in
`app/Http/Controllers/RecommendationController.php:154`, which distinguishes "another
request held the lock" from "the LLM provider failed" — conflating the two previously
surfaced a provider error to a user when nothing had actually failed.

**Reproducing the suite.** The project's test database is SQLite in-memory
(`phpunit.xml`), so a local `pdo_sqlite` extension is required. Under the project's own
container stack, MySQL 8.4 is provided and the suite runs as:

```
docker compose exec app ./vendor/bin/phpunit
```

> **Note on this revision's verification.** The listings in A-1 through A-6 were checked
> programmatically against the source tree: all 433 reproduced code lines are
> byte-identical to the corresponding lines in `app/` and `config/` (exact whitespace
> included), and every referenced file path and line citation resolves. The test suite
> itself was **not** executed as part of producing this appendix, so no pass/fail counts
> are asserted here.

---

### Corrections to project documentation

The following discrepancies between the repository's internal docs and the shipped code
were found while preparing this appendix. The report should follow the **code**, not the
docs:

| Claim | Source | Actual |
| --- | --- | --- |
| "Laravel 12" | `docs/project-overview.md:4` | Laravel 13.8 |
| "13 migrations" | `docs/project-overview.md` | 21 migrations |
| "25 models" | `docs/project-overview.md` | 26 models |
| `embedding` columns are a "deferred seam" | `docs/product-audit.md:161` | Dropped by `2026_09_26_000001_drop_unused_embedding_columns.php` |
| `GET /search` endpoint | `docs/product-audit.md:116` | No such route; the app has no API layer |

**ML scope.** The system contains exactly two trained models: a Random Forest
(`config/ml.php:58`, 500 estimators) and K-Means (`config/ml.php:37`, *k* = 6), and both
are used **only** to classify lines of resume text into section labels
(`app/Services/Ml/SectionClassifier.php`, `SectionClusterer.php`). They are not used for
recommendation or job-fit prediction. Holdout accuracy for section classification is
weak — the seeded trainer records a **17–26 %** range across runs
(`app/Console/Commands/MlTrain.php:56`), against a corpus too small for a 500-tree forest
(`MlTrain.php:229`). This is precisely why a dictionary-first heuristic
(`app/Services/Ml/ResumeSegmenter.php`) carries production traffic instead.
`docs/product-audit.md:35-40` is the authoritative statement of this boundary and should be
cited in preference to `docs/project-overview.md`.
