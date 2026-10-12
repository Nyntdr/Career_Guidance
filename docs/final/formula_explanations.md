# UjwolBato — Mathematical Formulas & Algorithmic Breakdown

This document provides a thorough explanation of all mathematical formulas, algorithmic mechanics, and parameter selections used throughout the **UjwolBato** career guidance platform.

---

## 1. Random Forest Argmax Ensemble Voting

### Mathematical Formula:
$$\hat{y}(h) = \arg\max_{c \in C} \left( \frac{1}{T} \sum_{t=1}^{T} P_t(y = c \mid h) \right)$$

### Parameter & Variable Definitions:
* $h$: A single resume line feature vector (228 total dimensions: 220 TF-IDF features + 8 structural scalar features).
* $C$: Set of 13 canonical resume section classes (`summary`, `skills`, `experience`, `education`, `projects`, `certifications`, `contact`, etc.).
* $T$: Total number of decision trees in the ensemble ($T = 500$).
* $P_t(y = c \mid h)$: Class probability output from an individual CART decision tree $t$ for category $c$.
* $\sum_{t=1}^{T}$: Aggregates probability votes across all $T$ independent trees.
* $\frac{1}{T}$: Normalizes the sum to calculate the ensemble mean posterior probability.
* $\arg\max_{c \in C}$: Selects the category label that achieves the highest mean probability.
* $\hat{y}(h)$: The final predicted section category for line $h$.

### How It Works & Why It Is Used:
Individual decision trees tend to suffer from high variance and can easily overfit to specific training phrases. By constructing $500$ trees using bootstrap sampling with random feature subsets, the ensemble averages out individual tree variance. The classification boundary becomes smoother and more reliable on unseen resume layouts.

---

## 2. K-Means Within-Cluster Sum of Squares (WCSS / Inertia)

### Mathematical Formula:
$$J = \sum_{i=1}^{K} \sum_{x \in C_i} \|x - \mu_i\|^2$$

### Parameter & Variable Definitions:
* $J$: The objective cost function (total within-cluster variance / inertia).
* $K$: Number of visual/structural clusters ($K = 6$).
* $C_i$: Subset of line feature vectors assigned to cluster $i$.
* $x$: Individual line feature vector.
* $\mu_i$: Centroid (mean center vector) of cluster $i$, calculated as $\mu_i = \frac{1}{|C_i|} \sum_{x \in C_i} x$.
* $\|x - \mu_i\|^2$: Squared Euclidean distance between the line vector and its assigned cluster centroid.

### How It Works & Why It Is Used:
K-Means iteratively reassigns points to their closest centroid and recalculates the centroid positions until $J$ converges to a local minimum. In UjwolBato, this groups visually and structurally similar lines (e.g., heading lines, short metadata bullets, long descriptions) into 6 distinct structural neighborhoods.

---

## 3. Neighborhood Majority-Vote Smoothing Rule

### Mathematical Formula:
$$\text{label}(x) = \text{majority-label}(C_i) \quad \text{if } |C_i| \ge 2 \text{ and } \text{purity}(C_i) \ge 0.70$$

### Parameter & Variable Definitions:
* $\text{label}(x)$: Final assigned category for line $x$.
* $|C_i| \ge 2$: Minimum cluster size threshold (prevents an isolated single line from dictating smoothed labels).
* $\text{purity}(C_i) \ge 0.70$: Minimum cluster purity requirement, where:
  $$\text{purity}(C_i) = \frac{\max_{c \in C} \sum_{x \in C_i} \mathbb{I}(\text{RF-label}(x) == c)}{|C_i|}$$
* $\text{majority-label}(C_i)$: The most frequent section label held by members in cluster $i$.

### How It Works & Why It Is Used:
When the Random Forest classifier produces an uncertain prediction (confidence $< 0.30$), heading switches can erroneously trigger on normal bullet points. This smoothing rule checks whether the surrounding geometric neighborhood overwhelmingly agrees (70%+ consensus). If so, it overrides the ambiguous label with the consensus label.

---

## 4. Weighted Skill Overlap Algorithm

### Mathematical Formula:
$$\text{Overlap Score} = \left(\frac{E_{\text{matched}}}{E_{\text{total}}}\right) \times 70 + \left(\frac{P_{\text{matched}}}{P_{\text{total}}}\right) \times 30$$

### Parameter & Variable Definitions:
* $E_{\text{matched}}$: Number of required **essential** skills the candidate possesses at or above the role's minimum proficiency level ($\text{candidate\_level} \ge \text{required\_level}$).
* $E_{\text{total}}$: Total number of essential skills defined for the target role ($E_{\text{total}} \ge 1$).
* $P_{\text{matched}}$: Number of **preferred** skills the candidate possesses (held at any level $\ge 1$).
* $P_{\text{total}}$: Total number of preferred skills defined for the role ($P_{\text{total}} \ge 1$).
* $70$ and $30$: Fixed percentage weights prioritizing core requirements over secondary preferences.

### How It Works & Why It Is Used:
Job roles distinguish between non-negotiable requirements (e.g., PHP for a Backend Laravel role) and bonus tools (e.g., Redis, Docker). Candidates receive up to 70 points for satisfying all core requirements and up to 30 bonus points for preferred proficiencies, yielding an intuitive 0–100 match scale.

---

## 5. Inverse Document Frequency (IDF) & The Role of the Logarithm

### Mathematical Formula:
$$\text{IDF}(t) = 1 + \log\left(\frac{N}{\text{DF}(t)}\right)$$

### Parameter & Variable Definitions:
* $t$: A distinct skill or n-gram term (e.g., "Laravel", "Python", "Communication").
* $N$: Total number of career roles / profiles indexed in the platform.
* $\text{DF}(t)$: **Document Frequency** — total count of career roles that contain term $t$.
* $1 + \dots$: Smoothing term ensuring every valid term maintains a baseline weight $> 0$.

### Why Does IDF Use a Logarithm ($\log$)?
1. **Dampening Extreme Frequency Disparities (Sub-linear Growth):**
   * If term $A$ appears in 1 role out of 1,000, $\frac{N}{\text{DF}} = 1,000$.
   * If term $B$ appears in 500 roles out of 1,000, $\frac{N}{\text{DF}} = 2$.
   * Without $\log$, term $A$ would be deemed **500 times more important** than term $B$. A single rare typo or niche buzzword would completely dominate and distort matching scores.
2. **Compressing Wide Ranges:**
   * Using the natural or common log, $\log(1000) \approx 6.91$ while $\log(2) \approx 0.69$.
   * The logarithm compresses an exponential range ($1000:2$) into a well-behaved linear scale (~$10:1$).
3. **Information-Theoretic Grounding:**
   * In Shannon Information Theory, the self-information (surprisal) of an event with probability $P$ is $I = -\log(P) = \log(1/P)$. Since $\frac{\text{DF}(t)}{N}$ is the probability of encountering term $t$, $\log\left(\frac{N}{\text{DF}(t)}\right)$ measures the exact information quantity carried by that term.

---

## 6. Cosine Similarity & Blended Recommendation Scoring

### Mathematical Formulas:
$$\text{Semantic Score} = \frac{\mathbf{u} \cdot \mathbf{r}}{\|\mathbf{u}\| \|\mathbf{r}\|} \times 100 = \frac{\sum_{t} u_t \cdot r_t}{\sqrt{\sum_{t} u_t^2} \sqrt{\sum_{t} r_t^2}} \times 100$$

$$\text{Final Score}(\text{role}) = 0.60 \times \text{Overlap Score} + 0.40 \times \text{Semantic Score}$$

### Parameter & Variable Definitions:
* $\mathbf{u}$: The user's skill vector (weighted by candidate proficiency and TF-IDF).
* $\mathbf{r}$: The target role's skill requirement vector (essential skills weighted $2\times$ over preferred, scaled by IDF).
* $\mathbf{u} \cdot \mathbf{r}$: Inner dot product, accumulating co-occurring skill weights.
* $\|\mathbf{u}\|, \|\mathbf{r}\|$: Euclidean L2 norms ($\sqrt{\sum x_i^2}$) measuring vector lengths.
* $0.60$ and $0.40$: Empirically tuned blend ratio combining hard requirement satisfaction with semantic orientation.

### How It Works & Why It Is Used:
Dividing the dot product by the product of vector norms ($\|\mathbf{u}\| \|\mathbf{r}\|$) normalizes for length. A senior user with 40 listed skills is not unfairly elevated over a junior user with 8 highly focused, relevant skills. The angle $\theta$ between the vectors evaluates topical orientation rather than volume.

---

## 7. BFS Graph Traversal with Inverse Hop Decay

### Mathematical Formula:
$$\text{Score}'(r) = \min\left(100, \; \text{Score}(r) + \frac{\text{Bonus}}{\text{Hops}(r)}\right) \quad \text{for } \text{Hops}(r) \in \{1, 2\}$$

### Parameter & Variable Definitions:
* $\text{Score}(r)$: The baseline blended score for role $r$.
* $\text{Bonus}$: Base adjacency incentive constant ($\text{Bonus} = 5.0$).
* $\text{Hops}(r)$: Graph shortest-path distance (number of directed edges) from the seed top-matched role to role $r$ in the `career_paths` database.
  * For $\text{Hops} = 1$ (immediate promotion or adjacent role): Boost = $+\frac{5.0}{1} = \mathbf{+5.0 \text{ points}}$.
  * For $\text{Hops} = 2$ (secondary career horizon): Boost = $+\frac{5.0}{2} = \mathbf{+2.5 \text{ points}}$.
* $\min(100, \dots)$: Clamps the score so no role exceeds the maximum ceiling of 100.

### How It Works & Why It Is Used:
Direct keyword and overlap matching only evaluates where a student is *today*. The Breadth-First Search (BFS) graph boost recommends logical forward-looking trajectories (e.g., Junior Web Developer $\to$ Full Stack Developer $\to$ Tech Lead) without distorting the direct ranking order.

---

## 8. Composite ATS Resume Scoring

### Mathematical Formula:
$$\text{ATS Score} = 0.50 \times S_{\text{keyword}} + 0.15 \times S_{\text{structure}} + 0.15 \times S_{\text{formatting}} + 0.20 \times S_{\text{readability}}$$

### Component Sub-Scores:
1. **Keyword Match ($S_{\text{keyword}}$ - 50% Weight):**
   * Evaluates alias-aware matching of required job skills ($70\%$ weight) and preferred skills ($30\%$ weight).
2. **Structure Score ($S_{\text{structure}}$ - 15% Weight):**
   * Rewards presence of four core resume sections (`summary`, `experience`, `education`, `skills`):
     $$S_{\text{structure}} = \left(\frac{\text{Found Core Sections}}{4}\right) \times 100$$
3. **Formatting Score ($S_{\text{formatting}}$ - 15% Weight):**
   * Synthesizes bullet point consistency (40%), strong action verb usage (30%), and quantified metric impact (30%).
4. **Readability Score ($S_{\text{readability}}$ - 20% Weight):**
   * Rescales the Flesch-Kincaid Grade Level into a normalized quality score clamped between 20 and 95.

---

## 9. Flesch-Kincaid Grade Level Formula

### Mathematical Formula:
$$\text{Grade} = 0.39 \left(\frac{W}{S}\right) + 11.8 \left(\frac{\text{SYL}}{W}\right) - 15.59$$

### Parameter & Variable Definitions:
* $W$: Total word count extracted from the resume.
* $S$: Total sentence count (segmented by terminal punctuation `[.!?]+`).
* $\text{SYL}$: Syllable count (estimated via vowel-cluster analysis with silent-'e' terminal reductions).
* $\frac{W}{S}$: **Average Sentence Length (ASL)** — measures syntactic complexity.
* $\frac{\text{SYL}}{W}$: **Average Syllables per Word (ASW)** — measures vocabulary density and jargon.
* $0.39, 11.8, -15.59$: Empirical coefficients derived from standard reading comprehension benchmarks.

### Interpretation in UjwolBato:
Resumes scoring at Grade 8–10 are rewarded with the highest readability scores because they are concise, scannable, and clear. Excessively verbose or overly dense prose ($> \text{Grade } 16$) is penalized to encourage punchy, recruiter-friendly phrasing.

---

## 10. Skill-Gap Candidate Readiness & Multi-Criteria Ranking

### Candidate Readiness Formula:
$$\text{Readiness Score} = \left(\frac{\sum_{k=1}^{M} \min(c_k, r_k)}{\sum_{k=1}^{M} r_k}\right) \times 100$$

### Multi-Criteria Resource Ranking Tuple:
$$\text{Rank}(\text{Resource}) = \Big( -\text{Coverage Count}, \; \text{Cost (Ascending)}, \; \text{Duration in Hours (Ascending)} \Big)$$

### Parameter & Variable Definitions:
* $c_k$: Candidate's current proficiency level in skill $k$ ($0 \le c_k \le 5$).
* $r_k$: Target role's required proficiency in skill $k$ ($1 \le r_k \le 5$).
* $\min(c_k, r_k)$: Caps candidate points at the required benchmark. Over-qualification in one skill cannot offset a deficiency in another skill.
* $-\text{Coverage Count}$: Primary sorting key. Selects courses teaching the largest number of missing skills first.
* $\text{Cost}$ and $\text{Duration}$: Secondary and tertiary tie-breakers prioritizing accessible and time-efficient learning materials.

---

## Summary Matrix

| Algorithm / Metric | Category | Primary Purpose | Key Equation / Logic |
| :--- | :--- | :--- | :--- |
| **Random Forest** | Supervised ML | Classifies resume lines into 13 sections | $\hat{y} = \arg\max \frac{1}{T}\sum P_t$ |
| **K-Means Clustering** | Unsupervised ML | Groups lines into 6 visual clusters | $\min \sum \|x - \mu\|^2$ |
| **Majority Vote Smoothing** | Post-Processing | Safety net for low RF confidence ($<0.30$) | Majority label if purity $\ge 0.70$, size $\ge 2$ |
| **Weighted Skill Overlap** | Deterministic | Hard requirement match | $70\% \cdot (E_{\text{match}}/E_{\text{total}}) + 30\% \cdot (P_{\text{match}}/P_{\text{total}})$ |
| **TF-IDF + Cosine** | Semantic ML | Semantic vector alignment | $\cos(\theta) = \frac{\mathbf{u}\cdot\mathbf{r}}{\|\mathbf{u}\|\|\mathbf{r}\|}$, $0.60 \text{ overlap} + 0.40 \text{ cos}$ |
| **BFS Hop Decay** | Graph Traversal | Promotes next-step career paths | $\text{Score} + \frac{5.0}{\text{hops}}$ |
| **Composite ATS** | Rule-Based Scoring | Comprehensive resume ATS grade | $0.50 K + 0.15 S + 0.15 F + 0.20 R$ |
| **Flesch-Kincaid** | Text Analytics | Reading ease analysis | $0.39(W/S) + 11.8(\text{SYL}/W) - 15.59$ |
| **Readiness & Ranking** | Gap Analysis | Skill-gap calculation and course selection | $\frac{\sum\min(c_k, r_k)}{\sum r_k} \times 100$; Top 3 by $[-\text{cov}, \text{cost}, \text{hrs}]$ |
