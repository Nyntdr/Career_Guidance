<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ML configuration
    |--------------------------------------------------------------------------
    |
    | Paths and default hyper-parameters for the resume-parsing models.
    | Runtime toggles (ml_enabled, ml_min_confidence, ml_cluster_purity)
    | are driven by the admin Settings under the "ML" group.
    */

    'model_path' => storage_path('app/models'),

    'training_path' => storage_path('app/ml/training'),

    'vocab_path' => storage_path('app/ml/vocab.json'),

    'corpus_file' => 'section_lines.json',

    // Minimum class probability for a Random Forest prediction to be
    // treated as a heading switch. Real fuzzy headings score ~0.35-0.55 while
    // short title-case content bullets reach ~0.4, so the structural gate
    // (blank-line isolation + paragraph buffering) does the real work here.
    'default_confidence' => 0.3,

    // Minimum share of a cluster's lines sharing the majority label for
    // low-confidence lines to inherit the cluster's dominant label.
    'cluster_purity' => 0.7,

    // A cluster must be at least this large for neighbourhood smoothing to
    // apply; a lone line carries no smoothing signal of its own.
    'min_cluster_size' => 2,

    // Number of clusters for the K-Means segmentation model.
    'k' => 6,

    // Size of the learned feature vocabulary, on top of the always-retained
    // ResumeLineVectorizer::BASE_VOCAB. Terms are ranked by corpus frequency,
    // not alphabetically, so this budget is spent on the most informative
    // features. Raise it when training on a large harvested corpus.
    'vocab_cap' => 220,

    // Minimum corpus frequency for a term to enter the learned vocabulary.
    'vocab_min_count' => 2,

    // Memory ceiling applied while ml:train runs. Training holds one dense
    // vector per sample, so a harvested corpus overruns the CLI default.
    'train_memory_limit' => '1G',

    // Seed for PHP's global RNG during training. Rubix's RandomForest bootstrap
    // and K-Means++ seeding both use rand(), so without a seed every run yields
    // a different model and a different holdout score. Set to null to disable.
    'train_seed' => 20260926,

    // Random Forest ensemble size and bootstrap ratio.
    'estimators' => 500,
    'ratio' => 0.3,

    // Fraction of the corpus held out for the classifier accuracy report.
    'holdout' => 0.2,
];
