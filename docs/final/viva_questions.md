# Possible Viva Questions & Answers

This document contains potential questions that examiners might ask during your final defense regarding the machine learning and algorithmic aspects of the Career Guidance System, along with suggested answers.

## 1. Machine Learning Models

**Q: Why did you choose a Random Forest Classifier for categorizing resume sections?**
**A:** Random Forest is an ensemble method, meaning it builds multiple decision trees (500 in our case) and merges them together. This makes it highly accurate and resistant to overfitting compared to a single decision tree. It's well-suited for our system because text data from resumes can be very noisy and varied, and the "forest" structure handles this variance gracefully by taking a majority vote across all trees.

**Q: What is the purpose of K-Means Clustering in your project, and why did you set `k` to 6?**
**A:** K-Means is used as a fallback and smoothing mechanism. When the Random Forest isn't confident about a specific line of text, K-Means looks at the document spatially and logically groups lines into clusters. If a low-confidence line falls into a cluster dominated by `education` lines, the system infers the low-confidence line is also `education`. We set `k=6` because standard resumes generally break down into around 6 major semantic blocks (e.g., Contact, Summary, Experience, Education, Skills, and Projects/Miscellaneous).

**Q: Where does the training data for your machine learning models come from?**
**A:** Our training data comes from three sources:
1. A manually labeled seed corpus (`section_lines.json`) containing high-quality examples.
2. "Weak labels" harvested dynamically from the `resume_sections` database table (learning from previously parsed resumes).
3. Test fixture text files used to extract more structural lines. 

## 2. Text Analysis & Heuristics

**Q: You mentioned using "Heuristics." What does that mean in the context of your project, and why do you use them before the ML models?**
**A:** Heuristics refer to rule-based logic—specifically dictionary matching and regular expressions (Regex). We use them as a "first line of defense." For example, if a line exactly matches "Professional Experience," our dictionary heuristic instantly tags it as `experience` without needing to run the heavier machine learning models. This saves computational power and speeds up the parsing process significantly.

**Q: How do you match a user's skills to a job's required skills?**
**A:** We use a combination of TF-IDF (Term Frequency-Inverse Document Frequency) and Cosine Similarity. 
- **TF-IDF** gives weight to the skills; rare or highly essential skills get a higher mathematical weight than common ones.
- **Cosine Similarity** compares the mathematical vector of the user's proficiencies against the job's requirement vector, giving us a final percentage match.

**Q: What happens if a user's skill level doesn't meet the job's minimum requirement?**
**A:** When building the vectors for Cosine Similarity, we check the user's `proficiency_level` against the role's `min_proficiency`. If the user doesn't meet the minimum requirement, that specific skill contributes 0 to their score, ensuring the system strictly enforces the minimum job requirements.

## 3. General Implementation

**Q: What library did you use for machine learning in PHP, and why not use Python?**
**A:** We used the **Rubix ML** library, which is a native PHP machine learning library. Keeping the ML pipeline in PHP allows us to tightly integrate it with our Laravel application, utilizing the same Eloquent models (like `ResumeSection`) and keeping the deployment stack simple without needing to maintain a separate Python microservice.
