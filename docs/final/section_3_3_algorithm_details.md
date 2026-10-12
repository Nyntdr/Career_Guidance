# Chapter 3: System Methodology & Design

---

## 3.3 Algorithm Details

**UjwolBato** is an intelligent career guidance platform designed to assist students and job seekers in selecting optimal career paths, identifying skill deficiencies, and evaluating resume readiness. The system integrates machine learning models, deterministic matching rules, and graph algorithms to process user profiles, resume text, and industry role benchmarks.

The algorithmic pipeline operates across three primary functional areas:
1. **Intelligent Resume Parsing:** Extracting and classifying unstructured resume lines into standardized sections using a hybrid supervised-unsupervised learning approach.
2. **Career Recommendation & Role Matching:** Calculating multidimensional candidate-to-role suitability scores combining hard skill overlap, semantic vector alignment, and graph traversal.
3. **ATS Resume & Skill-Gap Analysis:** Evaluating candidate resumes against target job postings using composite ATS metrics and ranking personalized learning resources.

---

### 3.3.1 Random Forest Classifier (Supervised Line Classification)

Random Forest is an ensemble supervised machine learning algorithm that builds multiple decision trees during training and aggregates their individual predictions to improve accuracy and control overfitting [9]. In **UjwolBato**, Random Forest classifies segmented non-empty lines from uploaded resumes into one of **13 canonical section categories** (*summary*, *skills*, *experience*, *education*, *projects*, *certifications*, *contact*, *awards*, *publications*, *languages*, *volunteer*, *interests*, and *references*).

Each line is represented by a 228-dimensional feature vector combining 220 frequency-ranked TF-IDF n-grams with 8 structural scalar properties (such as word count, title-case ratio, all-caps flag, and digit density). For multi-class classification, the final prediction is determined by taking the argmax over the average class probability distribution across all trees:

$$\hat{y}(h) = \arg\max_{c \in C} \left( \frac{1}{T} \sum_{t=1}^{T} P_t(y = c \mid h) \right)$$

**Where:**
* $\hat{y}(h)$ = Predicted section label for resume line $h$
* $C$ = Set of 13 canonical section labels
* $T$ = Total number of decision trees in the forest ($T = 500$)
* $P_t(y = c \mid h)$ = Posterior class probability predicted by individual tree $t$

---

### 3.3.2 K-Means Clustering (Unsupervised Neighborhood Smoothing)

K-Means is an unsupervised clustering algorithm that partitions feature vectors into $K$ distinct clusters by minimizing the within-cluster sum of squares (WCSS / Inertia) [10]. In **UjwolBato**, K-Means ($k=6$) is deployed on the resume line feature vectors as a post-processing **neighborhood smoothing layer**. When the Random Forest classifier yields low confidence for an ambiguous line (probability $< 0.30$), the system inspects its assigned K-Means structural cluster to correct false heading switches and smooth section boundaries.

The K-Means objective function minimizes the squared Euclidean distance to cluster centroids:

$$J = \sum_{i=1}^{K} \sum_{x \in C_i} \|x - \mu_i\|^2$$

A low-confidence line inherits the majority label of its assigned cluster according to the smoothing condition:

$$\text{label}(x) = \text{majority-label}(C_i) \quad \text{if } |C_i| \ge 2 \text{ and } \text{purity}(C_i) \ge 0.70$$

**Where:**
* $J$ = Total within-cluster variance (Inertia)
* $K$ = Number of structural clusters ($K = 6$)
* $C_i$ = Set of feature vectors assigned to cluster $i$
* $x$ = Line feature vector; $\mu_i$ = Centroid of cluster $i$
* $\text{purity}(C_i)$ = Proportion of cluster members sharing the dominant section label ($\ge 70\%$)

---

### 3.3.3 Weighted Skill Overlap Algorithm (Deterministic Role Matching)

To evaluate direct compatibility between a candidate's profile and a target career role or job opening, the platform executes a deterministic weighted overlap algorithm. Skill requirements are partitioned into **essential (70% weight)** and **preferred (30% weight)** categories. Essential requirements require candidate proficiency to meet or exceed the role threshold, whereas preferred skills count if held at any proficiency level:

$$\text{Overlap Score} = \left(\frac{E_{\text{matched}}}{E_{\text{total}}}\right) \times 70 + \left(\frac{P_{\text{matched}}}{P_{\text{total}}}\right) \times 30$$

**Where:**
* $E_{\text{matched}}, E_{\text{total}}$ = Matched and total required essential skills ($E_{\text{total}} \ge 1$)
* $P_{\text{matched}}, P_{\text{total}}$ = Matched and total required preferred skills ($P_{\text{total}} \ge 1$)

---

### 3.3.4 TF-IDF Cosine Similarity & Blended Recommendation Scoring

To capture semantic alignment and prevent vocabulary mismatch, user skill profiles and career roles are transformed into sparse weighted TF-IDF vector representations. Essential skills carry twice the weight of preferred skills ($2\times$), scaled by Inverse Document Frequency ($\text{IDF}$):

$$\text{IDF}(t) = 1 + \log\left(\frac{N}{\text{DF}(t)}\right)$$

$$\text{Semantic Score} = \frac{\mathbf{u} \cdot \mathbf{r}}{\|\mathbf{u}\| \|\mathbf{r}\|} \times 100 = \frac{\sum u_t \cdot r_t}{\sqrt{\sum u_t^2} \sqrt{\sum r_t^2}} \times 100$$

*The logarithm ($\log$) in IDF prevents rare niche terms from disproportionately skewing the score while dampening the influence of ubiquitous, common skills.*

**Final Recommendation Blended Score:**  
To leverage both strict proficiency matching and semantic breadth, the final role recommendation score combines overlap and cosine similarity:

$$\text{Final Score}(\text{role}) = 0.60 \times \text{Overlap Score} + 0.40 \times \text{Semantic Score}$$

---

### 3.3.5 Graph Traversal via BFS with Inverse Hop Decay

To recommend forward-looking career pathways without distorting baseline ranking accuracy, the system performs a **Breadth-First Search (BFS)** traversal over the `career_paths` directed transition graph starting from top-matching roles up to 2 hops:

$$\text{Score}'(r) = \min\left(100, \; \text{Score}(r) + \frac{\text{Bonus}}{\text{Hops}(r)}\right) \quad \text{for } \text{Hops}(r) \in \{1, 2\}$$

**System Parameters:**
* $\text{Bonus}$ = Base hop incentive constant (5.0 points)
* $\text{Hops}(r)$ = Graph edge distance ($+5.0$ points for 1-hop direct promotions, $+2.5$ points for 2-hop career horizons)

---

### 3.3.6 Composite ATS Resume Scoring Algorithm

The ATS Scanner evaluates uploaded candidate resumes against target job postings using a multi-factor composite scoring engine:

$$\text{ATS Score} = 0.50 \times S_{\text{keyword}} + 0.15 \times S_{\text{structure}} + 0.15 \times S_{\text{formatting}} + 0.20 \times S_{\text{readability}}$$

**Sub-Score Definitions:**
1. **Keyword Match ($S_{\text{keyword}}$ - 50%):** Word-boundary alias-aware matching of required ($70\%$) vs. preferred ($30\%$) skills.
2. **Structure ($S_{\text{structure}}$ - 15%):** Presence of 4 core sections (*summary*, *experience*, *education*, *skills*): $S_{\text{structure}} = (f / 4) \times 100$.
3. **Formatting ($S_{\text{formatting}}$ - 15%):** Weighted compliance checks for bullet points ($40\%$), action verbs ($30\%$), and quantified impact metrics ($30\%$).
4. **Readability ($S_{\text{readability}}$ - 20%):** Standardized score derived from the **Flesch-Kincaid Grade Level**:
   $$\text{Grade} = 0.39 \left(\frac{W}{S}\right) + 11.8 \left(\frac{\text{SYL}}{W}\right) - 15.59$$
   *(Where $W$ is total words, $S$ is sentence count, and $\text{SYL}$ is syllable count. Punchy grades between 8–10 achieve optimal scores).*

---

### 3.3.7 Skill-Gap Assessment & Multi-Criteria Resource Ranking

When a candidate targets an aspirational role, missing essential proficiencies trigger a skill-gap assessment.

**Candidate Readiness Score:**
$$\text{Readiness Score} = \left(\frac{\sum \min(c_k, r_k)}{\sum r_k}\right) \times 100$$
*(Where $c_k$ is the candidate's current skill level and $r_k$ is the required benchmark. Capping credit at $\min(c_k, r_k)$ ensures strong skills cannot mask critical deficits).*

**Learning Resource Ranking:**  
To provide actionable pathways to bridge identified gaps, relevant learning resources are ranked using a multi-criteria tuple:
$$\text{Rank}(\text{Resource}) = \Big( -\text{Coverage Count}, \; \text{Cost (Ascending)}, \; \text{Duration in Hours (Ascending)} \Big)$$

The system prioritizes resources that cover the maximum number of missing skills at the lowest cost and shortest duration.
