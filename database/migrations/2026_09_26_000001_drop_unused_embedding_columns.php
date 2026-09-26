<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop never-populated columns.
     *
     * `skills.embedding` and `job_postings.embedding` were reserved for a future
     * real-embedding tier, but nothing ever wrote or read them and the operative
     * semantic tier is the pure-PHP TF-IDF cosine in App\Services\Ml\SkillSimilarity.
     * `job_postings.parsed_requirements` was likewise never populated.
     *
     * Reintroducing them is a one-migration job when a real embedding provider
     * lands; until then they only let mass-assignment accept values nothing reads.
     */
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn('embedding');
        });

        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn('embedding');
            $table->dropColumn('parsed_requirements');
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->json('embedding')->nullable();
        });

        Schema::table('job_postings', function (Blueprint $table) {
            $table->json('embedding')->nullable();
            $table->json('parsed_requirements')->nullable();
        });
    }
};
