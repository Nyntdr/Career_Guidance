<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_recommendations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('profile_id');
            $table->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->uuid('career_role_id');
            $table->foreign('career_role_id')->references('id')->on('career_roles')->cascadeOnDelete();
            $table->decimal('match_score', 5, 2)->default(0);
            $table->text('rationale')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('progress_tracking', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('profile_id');
            $table->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->uuid('gap_assessment_item_id');
            $table->foreign('gap_assessment_item_id')->references('id')->on('gap_assessment_items')->cascadeOnDelete();
            $table->string('status')->default('not_started');
            $table->timestamps();
            $table->unique(['profile_id', 'gap_assessment_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_tracking');
        Schema::dropIfExists('career_recommendations');
    }
};
