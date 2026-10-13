# Chapter 4: System Evaluation & Results

---

## 4.3 Result Analysis

The **UjwolBato** machine learning pipeline was empirically evaluated on segmented resume line samples categorized across **13 canonical resume section classes** (*experience*, *skills*, *education*, *summary*, *projects*, *contact*, *certifications*, *languages*, *awards*, *publications*, *volunteer*, *interests*, and *references*).

To maintain strict methodological integrity and prevent data leakage:
- A **stratified 80:20 train-test split** was applied, preserving category proportions across both dominant sections (e.g., *Experience*, *Skills*) and sparse minority classes (e.g., *Awards*, *Publications*).
- **Leakage Prevention:** Vocabulary compilation, TF-IDF n-gram statistics (capped at 220 features), and the 8 line-level layout features (such as word count, title-casing ratio, all-caps flag, and digit density) were strictly fitted on the **80% training partition only**. The **20% holdout set** remained completely unseen until final evaluation.

---

### 4.3.1 Classification Performance & Scaling Progression

The supervised **Random Forest classifier** (configured with 500 decision trees and a 30% bootstrap subsample ratio) was evaluated across different training corpus volumes to observe scaling behavior:

| Model Setup | Dataset Size | Holdout Accuracy | Weighted Precision | Weighted Recall | Weighted F1-Score |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Random Forest (Initial Seed Corpus)** | 149 lines | 42.1% | 0.44 | 0.42 | 0.41 |
| **Random Forest (Harvested Corpus)** | 1,360 lines | 68.4% | 0.71 | 0.68 | 0.69 |
| **Hybrid Pipeline (RF + K-Means Smoothing + Structural Gate)** | 1,360 lines | **76.2%** | **0.78** | **0.76** | **0.77** |

**Key Findings:**
1. **Initial Baseline (42.1%):** In a complex 13-class classification problem where random chance is only $\approx 7.7\%$, an initial 42.1% accuracy on the small 149-line seed demonstrated that lexical and structural signals were informative, but vocabulary sparsity led to high variance on unseen phrasing.
2. **Corpus Expansion (68.4%):** Expanding the dataset to 1,360 lines yielded an immediate **+26.3% absolute accuracy gain**. This allowed the ensemble to reliably learn distinct syntactic indicators, such as date ranges and action verbs for *experience*, comma/pipe delimiters for *skills*, and degree acronyms (*BCA, B.Sc., MCA*) for *education*.
3. **Hybrid Neighborhood Smoothing (76.2%):** Integrating **K-Means clustering ($k=6$)** provided an effective fallback layer. When Random Forest predicted a line with low confidence ($< 30\%$), the line was reassigned to the dominant label of its visual/structural cluster (provided cluster purity was $\ge 70\%$). This eliminated isolated header-switch glitches and improved overall accuracy to **76.2%**.

---

### 4.3.2 Per-Class Performance and Ambiguity Analysis

Evaluation across individual categories revealed strong performance in high-impact sections, alongside expected linguistic overlap in descriptive sections:

* **High-Accuracy Sections ($F_1 > 0.80$):**
  * **Contact / Header ($F_1 = 0.92$):** Highly distinguishable due to digits, emails (`@`), URLs, and short line length.
  * **Skills ($F_1 = 0.83$):** Characterized by technical noun density, comma/pipe separators, and low sentence length.
  * **Experience ($F_1 = 0.84$) & Education ($F_1 = 0.80$):** Well-defined due to past-tense action verbs, year/date patterns, and university/degree tokens.

* **Primary Sources of Ambiguity:**
  * **Projects vs. Experience:** Bullets under personal projects (e.g., *"Built full-stack application using Laravel and Vue.js"*) share nearly identical syntax with employment history bullets. Enforcing a **paragraph buffer** (retaining the active section state across contiguous bullet blocks) resolved the majority of these cross-boundary errors.
  * **Minority Classes ($F_1 \approx 0.45\text{–}0.54$):** Infrequent sections such as *Awards*, *Volunteer*, and *Interests* contain fewer samples, leading to lower recall on atypical phrasing.

---

### 4.3.3 Downstream Platform Integration

Rather than treating the machine learning model as an isolated experiment, the output of this hybrid classifier feeds directly into the application's operational modules:
1. **ATS Resume Scoring:** The segmented sections are evaluated against job requirements using a composite scoring model ($50\%$ keyword match, $15\%$ structure completeness, $15\%$ formatting consistency, and $20\%$ Flesch-Kincaid readability), providing immediate, transparent feedback to candidates.
2. **Career Path Recommendation:** Extracted candidate skills are matched against defined industry roles using a blended formula ($60\%$ deterministic skill overlap + $40\%$ TF-IDF semantic cosine similarity).
3. **Graph-Based Promotion Trajectories:** A 2-hop **Breadth-First Search (BFS)** traversal over the career transition graph introduces hop-decayed score incentives (+5.0 points for 1 hop, +2.5 points for 2 hops) to highlight attainable next-step roles.
4. **Skill-Gap Analysis:** Unmatched competencies are prioritized using multi-criteria sorting ($-\text{coverage count}$, ascending cost, ascending duration), connecting students with the most cost-effective learning resources.
