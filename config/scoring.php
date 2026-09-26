<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Skill-overlap weights
    |--------------------------------------------------------------------------
    |
    | The importance split used by every deterministic skill-match score in the
    | app: career-role matching, job-posting matching, cold-start target-role
    | selection, and ATS keyword matching.
    |
    | These are config-only. They are deliberately *not* exposed as an admin
    | setting, because changing them shifts every existing score and there is
    | no re-scoring backfill. To make them runtime-tunable, add a
    | percent_group definition in config/settings.php and read it through
    | Settings::get() in RoleMatchScorer::weights() instead of config().
    |
    | `essential` maps onto job postings whose importance is `required`.
    */

    'essential_weight' => 70,

    'preferred_weight' => 30,

];
