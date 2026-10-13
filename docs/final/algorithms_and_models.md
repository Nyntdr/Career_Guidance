# Algorithms & Machine Learning Models Used in CareerGuide AI

This document provides a comprehensive overview of the algorithms, heuristics, and machine learning models utilized across the various modules of the Career Guidance System.

---

## 1. Machine Learning Classifiers

### Random Forest Classifier
*   **Where it is used:** Resume Parser Service (`app/Services/ResumeParser.php`).
*   **Purpose:** To categorize unlabelled blocks of text extracted from uploaded resumes into structured sections (e.g., Experience, Education, Skills, Summary, Projects).
*   **Implementation:** Implemented using the native PHP **Rubix ML** library. It builds an ensemble of 500 decision trees. Because resume text is highly variable and often noisy, the forest structure prevents overfitting and yields high accuracy by taking a majority vote across all trees.

### K-Means Clustering
*   **Where it is used:** Resume Parser Service (Smoothing/Fallback).
*   **Purpose:** Acts as a spatial fallback mechanism when the Random Forest classifier yields low confidence for a specific line of text.
*   **Implementation:** The algorithm is set to `k=6` clusters, representing the standard structural breakdown of a resume (Contact, Summary, Experience, Education, Skills, Miscellaneous). If a low-confidence line falls into a spatial cluster dominated by "Education" lines, the system infers the unknown line also belongs to the Education section.

---

## 2. Recommendation & Matching Algorithms

### TF-IDF (Term Frequency-Inverse Document Frequency)
*   **Where it is used:** AI Career Recommendations & Job Matching (`app/Services/Ml/SkillSimilarity.php`).
*   **Purpose:** To generate mathematical weights for skills based on their uniqueness and importance. Rare or highly essential skills (e.g., "TensorFlow") are assigned a higher weight than common ones (e.g., "Microsoft Word").

### Cosine Similarity
*   **Where it is used:** AI Career Recommendations & Job Matching.
*   **Purpose:** To compute the final semantic match score between a candidate and a target job or career role.
*   **Implementation:** The system converts the user's current proficiencies and the job's requirements into high-dimensional vectors (using the TF-IDF weights). Cosine Similarity calculates the angle between these vectors. A smaller angle (closer to 1.0) represents a higher match percentage. It also strictly enforces minimum proficiency levels (if a user doesn't meet the `min_proficiency`, the contribution for that skill is zeroed out).

### Hop-Decayed Breadth-First Search (BFS)
*   **Where it is used:** Career Path Recommendations (`app/Services/RecommendationEngine.php`).
*   **Purpose:** To give algorithmic priority (a score bonus) to career paths that represent logical, progressive steps from the user's current role.
*   **Implementation:** The system uses graph traversal over the `career_paths` table. Roles that are reachable within 1 or 2 "hops" from the user's current best-matched role receive a `recommendation_path_bonus` (e.g., Data Analyst -> Data Scientist). The bonus decays the further away the role is in the graph.

---

## 3. ATS Scoring & Text Analysis (Deterministic Algorithms)

### Flesch-Kincaid Readability Formula
*   **Where it is used:** ATS Scanning Engine (`app/Services/Readability.php`).
*   **Purpose:** To calculate the `readability_score` of an uploaded resume.
*   **Implementation:** A pure mathematical algorithm that counts total words, sentences, and syllables to determine the grade level of the text. The system scales this output to a 0–100 score, penalizing text that is overly complex or poorly structured.

### Dictionary Matching & Regex Heuristics
*   **Where it is used:** Resume Parsing and Skill Extraction.
*   **Purpose:** Acts as a highly performant "first line of defense" before invoking heavy ML models.
*   **Implementation:** The system uses regular expressions and a predefined dictionary of exact term matches. If a resume line exactly matches a known header like "Professional Experience" or a known skill alias from the `skill_aliases` table (e.g., "JS" -> "JavaScript"), it is tagged instantly. This drastically reduces processing overhead.

### Weighted Blend Scoring
*   **Where it is used:** ATS Scoring Engine (`app/Services/AtsScanner.php`) & Job Matching.
*   **Purpose:** To compute the overall match score.
*   **Implementation:** An aggregation algorithm that blends multiple sub-scores. For example, the overall ATS score is a blend of:
    *   Keyword Match Score (50%)
    *   Readability Score (20%)
    *   Structure Score (15%)
    *   Formatting Score (15%)
    *   *Note: Essential vs. Preferred skills are also weighted 70/30 dynamically.*
