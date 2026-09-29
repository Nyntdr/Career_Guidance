# UjwolBato — Algorithms Documentation & Cheat Sheet

---

## Part 1: Academic Report Section (Section 3.3)

### 3.3 Machine Learning & Core Algorithms

#### 3.3.1 Random Forest Classifier (Supervised ML)
Random Forest is an ensemble supervised learning algorithm that constructs multiple decision trees during training and merges their predictions to improve accuracy and control overfitting. In **UjwolBato**, Random Forest is used for **resume section line classification**, predicting which of the 13 canonical section categories (e.g., *experience*, *skills*, *education*, *summary*, *projects*, *contact*) a given resume line belongs to. 

For multi-class classification, the final prediction is determined by taking the argmax over the average class probability distribution across all trees:

$$\hat{y}(h) = \arg\max_{c \in C} \frac{1}{T} \sum_{t=1}^{T} P_t(y = c \mid h)$$

**Where:**
* $\hat{y}(h)$ = Predicted section label for resume line $h$
* $C$ = Set of 13 canonical section labels
* $T$ = Total number of decision trees in the forest
* $P_t(y = c \mid h)$ = Predicted probability distribution produced by tree $t$

**System Configuration & Parameters in UjwolBato:**
* **Number of Trees ($T$):** 500 CART decision trees (Gini splitting criterion)
* **Bootstrap Subsample Ratio:** 0.3 (each tree sees 30% of training data)
* **Confidence Floor Threshold:** 0.30 (predictions below 30% confidence are rejected)
* **Dataset Partitioning:** Stratified **80:20** train/test holdout split
* **Training Reproducibility Seed:** `20260926` (`mt_srand` seeded random fitting)
* **Feature Vector Composition (228 total dimensions):**
  * **220 TF-IDF n-gram vocabulary terms** (unigrams, bigrams, trigrams capped by frequency)
  * **8 line-structure scalar features:** normalized word count, title-case ratio, all-caps flag, digit density, trailing colon flag, trailing period flag, mean word length, and character length.

---

#### 3.3.2 K-Means Clustering (Unsupervised ML & Neighborhood Smoothing)
K-Means is an unsupervised clustering algorithm that partitions data points into $K$ distinct clusters based on feature similarity. In **UjwolBato**, K-Means is applied to the same resume line feature vectors to perform **neighborhood majority-vote smoothing**. When the Random Forest classifier yields low confidence for a line, the system checks its assigned K-Means cluster to correct spurious heading switches and smooth section boundaries.

The objective function of K-Means minimizes the within-cluster sum of squares (WCSS):

$$J = \sum_{i=1}^{K} \sum_{x \in C_i} \|x - \mu_i\|^2$$

A low-confidence line inherits the majority label of its cluster according to the rule:

$$\text{label}(x) = \text{majority-label}(C_i) \quad \text{if } |C_i| \ge 2 \text{ and } \text{purity}(C_i) \ge 0.70$$

**Where:**
* $J$ = Total clustering error (within-cluster variance)
* $K$ = Number of structural clusters
* $C_i$ = Set of feature vectors assigned to cluster $i$
* $x$ = Line feature vector
* $\mu_i$ = Centroid of cluster $i$
* $\|x - \mu_i\|^2$ = Squared Euclidean distance between line vector and cluster centroid
* $\text{purity}(C_i)$ = Proportion of cluster members sharing the dominant label

**System Configuration & Parameters in UjwolBato:**
* **Number of Clusters ($K$):** 6
* **Centroid Initialization:** k-means++ seeding
* **Distance Metric:** Euclidean distance ($\|\cdot\|_2$)
* **Minimum Cluster Size:** $\ge 2$ lines
* **Cluster Purity Threshold:** $\ge 0.70$ (70% majority consensus required for smoothing)

---

#### 3.3.3 Weighted Skill Overlap Algorithm (Role & Job Matching)
To assess how closely a user's profile matches a career role or job posting, UjwolBato uses a deterministic weighted overlap algorithm. Requirements are split into **essential (70% weight)** and **preferred (30% weight)** skill sets. Essential skills require candidate proficiency to meet or exceed the role threshold, whereas preferred skills count if held at any level.

$$\text{Overlap Score} = \left(\frac{E_{\text{matched}}}{E_{\text{total}}}\right) \times 70 + \left(\frac{P_{\text{matched}}}{P_{\text{total}}}\right) \times 30$$

**Where:**
* $E_{\text{matched}}, E_{\text{total}}$ = Matched and total essential skills ($E_{\text{total}} \ge 1$)
* $P_{\text{matched}}, P_{\text{total}}$ = Matched and total preferred skills ($P_{\text{total}} \ge 1$)

---

#### 3.3.4 TF-IDF Weighted Cosine Similarity (Semantic Skill Alignment)
To capture lexical and semantic similarity between candidate skill profiles and target career roles, the system represents profiles as sparse weighted TF-IDF term vectors. Essential skills are weighted $2\times$ higher than preferred skills, scaled by Inverse Document Frequency ($\text{IDF}$):

$$\text{IDF}(t) = 1 + \log\left(\frac{N}{\text{DF}(t)}\right)$$

$$\text{Semantic Score} = \frac{\mathbf{u} \cdot \mathbf{r}}{\|\mathbf{u}\| \|\mathbf{r}\|} \times 100 = \frac{\sum u_t \cdot r_t}{\sqrt{\sum u_t^2} \sqrt{\sum r_t^2}} \times 100$$

**Final Recommendation Blended Score:**
Career recommendations combine direct overlap and semantic cosine similarity using an admin-configurable blend:

$$\text{Final Score}(\text{role}) = 0.60 \times \text{Overlap Score} + 0.40 \times \text{Semantic Score}$$

---

#### 3.3.5 Graph Traversal via Breadth-First Search (BFS) with Hop Decay
To recommend next-step and adjacent career trajectories without disturbing direct match rankings, UjwolBato executes a **Breadth-First Search (BFS)** over the `career_paths` directed graph starting from top-matching seed roles up to **2 hops**:

$$\text{Score}'(r) = \min\left(100, \text{Score}(r) + \frac{\text{Bonus}}{\text{Hops}(r)}\right) \quad \text{for } \text{Hops}(r) \in \{1, 2\}$$

**System Parameters:**
* **Base Hop Bonus:** 5 points
* **Max Search Depth:** 2 hops

---

#### 3.3.6 Composite ATS Resume Scoring Algorithm
The ATS Scanner evaluates uploaded candidate resumes against target job postings using a composite scoring model comprising four weighted sub-scores:

$$\text{ATS Score} = 0.50 \times S_{\text{keyword}} + 0.15 \times S_{\text{structure}} + 0.15 \times S_{\text{formatting}} + 0.20 \times S_{\text{readability}}$$

**Sub-Score Definitions:**
1. **Keyword Match ($S_{\text{keyword}}$ - 50%):** Word-boundary alias-aware matching of required ($70\%$) vs. preferred ($30\%$) skills.
2. **Structure ($S_{\text{structure}}$ - 15%):** Presence of 4 core sections (*summary*, *experience*, *education*, *skills*): $S_{\text{structure}} = (f / 4) \times 100$.
3. **Formatting ($S_{\text{formatting}}$ - 15%):** Normalized quality checks for bullet points ($40\%$), action verbs ($30\%$), and quantified achievements ($30\%$).
4. **Readability ($S_{\text{readability}}$ - 20%):** Score derived from the Flesch-Kincaid Grade Level index.

---

#### 3.3.7 Flesch-Kincaid Grade Level Algorithm (Readability Analysis)
To measure candidate resume readability within the ATS subsystem, UjwolBato implements the standard Flesch-Kincaid Grade Level formula:

$$\text{Grade} = 0.39 \left(\frac{W}{S}\right) + 11.8 \left(\frac{\text{SYL}}{W}\right) - 15.59$$

**Where:**
* $W$ = Total word count
* $S$ = Total sentence count
* $\text{SYL}$ = Syllable count (vowel-group estimation heuristic with silent-'e' adjustments)

*Grade levels are rescaled into an ATS sub-score clamped between 20 and 95 points.*

---

#### 3.3.8 Skill-Gap Assessment & Multi-Criteria Resource Ranking
When a candidate selects a target career role, missing essential skills trigger a skill-gap assessment.

**Candidate Readiness Score:**

$$\text{Readiness Score} = \left(\frac{\sum \min(c_k, r_k)}{\sum r_k}\right) \times 100$$

Where $c_k$ is the candidate's current skill level and $r_k$ is the required skill level.

**Learning Resource Ranking:**
For each identified skill gap, learning resources are ranked using a multi-criteria tuple:

$$\text{Rank}(\text{Resource}) = \Big( -\text{Coverage Count}, \; \text{Cost (Ascending)}, \; \text{Duration in Hours (Ascending)} \Big)$$

The system selects the **top 3** learning resources that cover the maximum number of identified skill gaps at the lowest cost and duration.

---

## Part 2: Quick Reference Cheat Sheet & Examples

### 🚀 UjwolBato Algorithm Cheat Sheet

#### 1. Random Forest Classifier (Supervised ML)
* **What it is:** A group of 500 decision trees that vote on how to classify text.
* **Job in UjwolBato:** Reads line-by-line in an uploaded resume and decides which section label it belongs to (*experience*, *skills*, *education*, *summary*, etc.).
* **Simple Example:**
  * **Input Line:** `"Python, JavaScript, Docker, PostgreSQL"`
  * **Tree #1 votes:** `skills`
  * **Tree #2 votes:** `skills`
  * **Tree #500 votes:** `skills`
  * **Result:** Line labeled as `skills` with high confidence ($98\%$).

---

#### 2. K-Means Clustering ($k=6$) (Unsupervised ML)
* **What it is:** Groups similar lines into 6 visual "neighborhoods" based on text features.
* **Job in UjwolBato:** Acts as a safety net. If Random Forest is confused about a line (confidence $< 30\%$), K-Means looks at the neighboring lines in that cluster to fix the mistake (**Majority Vote Smoothing**).
* **Simple Example:**
  * **Resume Line:** `"Problem Solving & Team Management"`
  * **Random Forest:** Unsure (Confidence is only $28\%$, below the $30\%$ threshold).
  * **K-Means Check:** This line belongs to **Cluster #3**, where $85\%$ of similar lines are `skills`.
  * **Result:** Label overridden and corrected to `skills`.

---

#### 3. Weighted Skill Overlap (70 / 30) (Deterministic Matching)
* **What it is:** A weighted percentage calculator that grades how well a candidate's skills match job requirements.
* **Job in UjwolBato:** Evaluates hard skill fit ($70\%$ weight for Essential skills, $30\%$ weight for Preferred skills).
* **Simple Example:**
  * **Target Role (Full-Stack Developer):**
    * *Essential Skills (70% weight):* `PHP`, `MySQL` $\rightarrow$ User has both ($2/2 = 100\%$) $\rightarrow$ **70 points**
    * *Preferred Skills (30% weight):* `Docker`, `AWS` $\rightarrow$ User has Docker only ($1/2 = 50\%$) $\rightarrow$ **15 points**
  * **Result:** Final Match Score = $70 + 15 = \mathbf{85 / 100}$.

---

#### 4. TF-IDF Cosine Similarity (Semantic Skill Alignment)
* **What it is:** Measures the geometric "angle" between a candidate's skill vector and a career role's skill vector. Rare skills count for more!
* **Job in UjwolBato:** Finds hidden semantic matches even when skill list lengths or names vary slightly.
* **Simple Example:**
  * **User Skills Vector:** `[Laravel, Vue.js, MySQL]`
  * **Role A (Web Dev) Vector:** `[Laravel, PHP, Vue.js]` $\rightarrow$ Small angle $\rightarrow$ **92% Semantic Match**
  * **Role B (Data Scientist) Vector:** `[PyTorch, CUDA, R]` $\rightarrow$ 90° angle $\rightarrow$ **8% Semantic Match**
  * **Final Recommendation Score:** $60\% \text{ (Overlap)} + 40\% \text{ (Semantic)} = \text{Blended Rank}$.

---

#### 5. BFS Graph Traversal with Hop Decay (Career Path Boost)
* **What it is:** A graph-walking algorithm that explores future career paths connected to your current skills.
* **Job in UjwolBato:** Looks 1 or 2 steps ahead in the `career_paths` database to suggest next-step promotions or adjacent careers.
* **Simple Example:**
  * **Current Match:** *Junior Software Engineer* ($\text{Base Score} = 90$)
  * **Graph Hops:**
    * **1 Hop Away (*Senior Software Engineer*):** Gets $+ (5 / 1) = \mathbf{+5 \text{ Bonus Points}}$.
    * **2 Hops Away (*Tech Lead / Architect*):** Gets $+ (5 / 2) = \mathbf{+2.5 \text{ Bonus Points}}$.

---

#### 6. Composite ATS Resume Scoring (Composite Metric)
* **What it is:** Combines 4 sub-checks into a single ATS score out of 100.
* **Job in UjwolBato:** Tells users how likely an automated system (ATS) will pass their resume.
* **Simple Example:**
  1. **Keyword Match (50%):** $80 \times 0.50 = 40.0$
  2. **Section Structure (15%):** Has all 4 main sections ($100 \times 0.15 = 15.0$)
  3. **Formatting & Action Verbs (15%):** Good bullet points ($70 \times 0.15 = 10.5$)
  4. **Readability Grade (20%):** Good reading level ($90 \times 0.20 = 18.0$)
  * **Total ATS Score:** $40.0 + 15.0 + 10.5 + 18.0 = \mathbf{83.5 / 100}$.

---

#### 7. Flesch-Kincaid Grade Level (Readability Metric)
* **What it is:** Calculates reading difficulty based on word length and sentence length.
* **Job in UjwolBato:** Ensures resume sentences are punchy and easy for hiring managers and ATS bots to scan.
* **Simple Example:**
  * *"Built scalable REST APIs using Laravel and reduced server latency by 40%."* 
    $\rightarrow$ **Grade 9** (Clear, punchy $\rightarrow$ **High ATS Readability Score**).
  * *"I was comprehensively involved in multi-faceted infrastructural paradigm shifts utilizing complex web utilities..."* 
    $\rightarrow$ **Grade 19** (Too dense/pretentious $\rightarrow$ **Lower ATS Score**).

---

#### 8. Skill-Gap & Multi-Criteria Resource Ranking
* **What it is:** Calculates your readiness percentage and picks the best learning courses to fill missing skill gaps.
* **Job in UjwolBato:** Ranks recommended courses by **Highest Skills Taught $\rightarrow$ Lowest Cost $\rightarrow$ Shortest Duration**.
* **Simple Example:**
  * **Candidate Missing Gaps:** `Docker` and `Kubernetes`.
  * **Course Option A:** Teaches `Docker` only | Costs \$50 | 10 Hours
  * **Course Option B:** Teaches `Docker` AND `Kubernetes` | Costs \$20 | 12 Hours
  * **Selection:** **Course B** wins because it covers **2 missing skills** instead of 1, even though it is 2 hours longer!

---

### 📋 Summary Matrix for Quick Reference

| Algorithm | Type | Primary Function | Key Parameter to Remember |
| :--- | :--- | :--- | :--- |
| **Random Forest** | Supervised ML | Classifies resume lines into 13 sections | 500 trees, 80:20 split, 220 vocab terms |
| **K-Means** | Unsupervised ML | Smooths out low-confidence section labels | $k=6$, Euclidean distance, $70\%$ purity |
| **Weighted Overlap** | Deterministic | Hard skill matching | $70\%$ Essential / $30\%$ Preferred |
| **TF-IDF Cosine** | Semantic | Soft/lexical skill similarity | Blended: $60\%$ Overlap + $40\%$ Semantic |
| **BFS Graph Search** | Graph Algorithm | Promotes next-step career paths | Max 2 hops, 5 point base bonus |
| **Composite ATS** | Rule-based Engine | Resume match score (0–100) | $50\%$ Keywords / $15\%$ Struct / $15\%$ Fmt / $20\%$ Read |
| **Flesch-Kincaid** | Text Formula | Readability grade score | Word/Sentence & Syllable/Word ratios |
| **Multi-Criteria Sorting** | Sorting Priority | Ranks courses for skill gaps | Top 3 by: Coverage $\rightarrow$ Cost $\rightarrow$ Duration |
