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

    // Random Forest ensemble size and bootstrap ratio.
    'estimators' => 500,
    'ratio' => 0.3,

    // Fraction of the corpus held out for the classifier accuracy report.
    'holdout' => 0.2,
];
