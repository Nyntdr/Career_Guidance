# Recommendations and ML Training Architecture

## 1. What Happens to Recommendations upon Uploading a Resume?
When you upload a new resume, the `ResumeController` extracts any skills found in the text. 
If the newly extracted skills add *new* unique skills to the user's overall `Profile` that they didn't already have, it triggers the `RecommendationEngine` to regenerate their career recommendations.

During regeneration, the first thing the engine does (`app/Services/RecommendationEngine.php` line 28) is:
```php
CareerRecommendation::where('profile_id', $profile->id)->delete();
```
**Conclusion:** The **old recommendations are completely wiped** (the system does not keep a history of past recommendations). The system then generates a brand new set of recommendations based on the user's updated, aggregated list of skills and saves them.

## 2. Where Recommendations Get Stored
The regenerated recommendations are saved directly into your MySQL database in the `career_recommendations` table. 
- **Eloquent Model:** `app/Models/CareerRecommendation.php`
- **Engine Logic:** `app/Services/RecommendationEngine.php` stores them via the `CareerRecommendation::create([...])` method in a loop at the end of the calculation.

## 3. Files Responsible for ML Training
The machine learning component (specifically used for Resume Section Classification) is trained manually via an Artisan command.
- **Training Command File:** `app/Console/Commands/MlTrain.php`
  - *What it does:* This file loads the dataset, sets up the Rubix ML estimators (Random Forest and K-Means), processes the feature vectors, fits the data, and saves the trained `.rbx` models to your storage disk.
- **Execution / Service Files:** 
  - `app/Services/Ml/SectionClassifier.php` (Runs predictions using the Random Forest)
  - `app/Services/Ml/SectionClusterer.php` (Runs the K-Means smoothing)
  - `app/Services/Ml/ResumeLineVectorizer.php` (Converts words into normalized numerical arrays for the models)
  - `app/Services/Ml/ModelRepository.php` (Handles saving and loading the compiled model binaries from the disk)

## 4. Where the ML Dataset is Located
The raw training dataset (corpus) containing labeled lines of resumes (e.g., text explicitly labeled as `experience`, `education`, etc.) is stored as a JSON file:
- **Dataset Path:** `database/ml/section_lines.json`

When you run the command `php artisan ml:train`, the script reads this JSON dataset, vectorizes the text, trains the machine learning models, and outputs the serialized model artifacts into the `storage/app/ml/` directory.
