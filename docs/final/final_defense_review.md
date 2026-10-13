# Final Defense Review: Machine Learning Algorithms & Training Data

This document provides a comprehensive overview of the machine learning aspects of the **Career Guidance System**, detailing the algorithms used, the files responsible for training, dataset sources, and how these models are applied within the system.

## 1. Machine Learning Algorithms Used

The project utilizes the **Rubix ML** library for PHP to implement two primary algorithms for segmenting and classifying sections of user resumes.

### A. Random Forest Classifier
**Definition**: Random Forest is an ensemble learning algorithm that builds multiple decision trees during the training phase. When making a prediction, it merges the output of all trees to provide a more accurate and stable classification than a single tree could. It's excellent for categorizing text into predefined buckets.

**How it's used in the project**:
The Random Forest model predicts which specific section a line of text from a resume belongs to (e.g., classifying "B.Sc. in Computer Science" as `education`, or "Java, Python, PHP" as `skills`).

**Project Parameters (`config/ml.php`)**:
- `estimators` (500): The number of decision trees in the "forest".
- `ratio` (0.3): The feature sampling ratio used to inject randomness and prevent overfitting.
- `holdout` (0.2): 20% of the data is held back during training to test the accuracy of the model.

### B. K-Means Clustering
**Definition**: K-Means is an unsupervised machine learning algorithm that groups similar data points together into a predefined number of clusters (*k*). It works by finding the center (mean) of each cluster and assigning data points to the closest center.

**How it's used in the project**:
It acts as a "clusterer" to logically group lines of a resume together based on their vocabulary and structure, even if the system is unsure of the exact label. This helps smooth out predictions—if a low-confidence line is clustered with high-confidence `experience` lines, the system can infer it also belongs to `experience`.

**Project Parameters (`config/ml.php`)**:
- `k` (6): The algorithm groups the resume data into 6 distinct clusters.
- `cluster_purity` (0.7): Minimum share of a cluster's lines that must share the same label to confidently re-label uncertain lines.

### C. TF-IDF & Cosine Similarity
**Definition**: TF-IDF (Term Frequency-Inverse Document Frequency) measures how important a specific word is to a document within a larger corpus. Cosine Similarity is a mathematical way to measure how similar two vectors (lists of numbers) are, regardless of their size.

**How it's used in the project**:
Located in `app/Services/Ml/SkillSimilarity.php`, this algorithm compares a user's skills against a job role's required skills. It creates a vector for the user's proficiencies and a vector for the role's requirements, weights them using TF-IDF (so rare skills carry more weight than common ones), and calculates their cosine similarity to provide a match percentage.

**Project Parameters Used**:
- `min_proficiency`: The user's skill level must meet the role's minimum proficiency to count.
- `importance`: If a job marks a skill as "essential," its mathematical weight in the role's vector is doubled (multiplied by 2).

### D. Dictionary/Structural Heuristics (Rule-Based NLP)
**Definition**: Heuristics are practical, rule-based approaches to problem-solving. In Natural Language Processing (NLP), this often means using predefined dictionaries, regex (regular expressions), and structural clues rather than relying entirely on probability.

**How it's used in the project**:
Located in `app/Services/ResumeParser.php`, heuristics serve as the first line of defense for parsing resumes:
- **Heading Matching**: The parser checks lines against a hardcoded dictionary of common headings (e.g., mapping "core competencies" to the `skills` section). If a perfect dictionary match is found, it avoids calling the heavy ML models.
- **Skill Extraction**: It uses Regex boundary matching (`\b...\b`) to scan the raw resume text against the database of known skills and their aliases, extracting them instantly.

---

## 2. Files Responsible for Training

The training pipeline is localized to specific command and service files:

- **`app/Console/Commands/MlTrain.php`**: This is the core artisan command (`php artisan ml:train`) responsible for orchestrating the training. It reads the dataset, vectorizes the text, trains both the Random Forest and K-Means models, and saves the generated models to the server.
- **`app/Services/Ml/ResumeLineVectorizer.php`**: Responsible for "vectorizing" text—meaning it converts human-readable resume lines into mathematical arrays (vectors) that the ML algorithms can understand.
- **`app/Services/Ml/ModelRepository.php`**: Handles the saving of the newly trained models (e.g., `resume_section_classifier.model`) into the `storage/app/models/` directory so they can be loaded quickly during real-time resume parsing.

---

## 3. Dataset and Affected Tables

The models learn how to classify resume sections by analyzing labeled text. The data is pulled from three main sources during the training phase:

1. **The Seed Corpus (`database/ml/section_lines.json`)**:
   - A shipped JSON file containing manual, high-quality examples of resume lines mapped to labels like `contact`, `experience`, `education`, etc.

2. **The Database Tables (`resume_sections` table)**:
   - When training, the system can optionally harvest "weak labels" directly from the database. It queries the `resume_sections` table (using `ResumeSection::query()`) to extract real text that users have previously saved under valid section types.

3. **Test Fixtures (`tests/Fixtures/resumes/*.txt`)**:
   - The system can also parse raw text files from the test suite to harvest additional training lines dynamically.

---

## 4. Simple Examples

### Example 1: Random Forest (Classification)
Imagine you give the system the following line from a resume:
> *"Led a team of 5 developers to build a predictive analytics dashboard."*

**Process**:
1. The `ResumeLineVectorizer` converts the words ("led", "team", "developers", "dashboard") into numbers.
2. The **Random Forest** passes these numbers through its 500 decision trees.
3. Most trees vote that this vocabulary is heavily associated with the `experience` or `projects` label.
4. **Result**: The algorithm outputs `experience` with a confidence score of `0.85`.

### Example 2: K-Means Clustering (Grouping)
Imagine a resume has a badly formatted list of items with no clear heading:
> *"2018 - 2022"*
> *"University of Technology"*
> *"Bachelor of Science"*

**Process**:
1. The **Random Forest** confidently labels "University of Technology" and "Bachelor of Science" as `education`, but is unsure about "2018 - 2022" (low confidence).
2. The **K-Means Clusterer** looks at the document structure and mathematically groups all three lines into the same spatial cluster (e.g., Cluster 3) because they appear close together and share context.
3. **Result**: Because Cluster 3 is predominantly made of `education` lines, the system overrides the low confidence on "2018 - 2022" and categorizes the entire block as `education`.
