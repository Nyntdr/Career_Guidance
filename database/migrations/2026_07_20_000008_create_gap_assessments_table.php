<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gap_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('profile_id');
            $table->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->uuid('target_role_id')->nullable();
            $table->foreign('target_role_id')->references('id')->on('career_roles')->nullOnDelete();
            $table->uuid('target_job_posting_id')->nullable();
            $table->foreign('target_job_posting_id')->references('id')->on('job_postings')->nullOnDelete();
            $table->decimal('overall_readiness_score', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('gap_assessment_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('gap_assessment_id');
            $table->foreign('gap_assessment_id')->references('id')->on('gap_assessments')->cascadeOnDelete();
            $table->uuid('skill_id');
            $table->foreign('skill_id')->references('id')->on('skills')->cascadeOnDelete();
            $table->unsignedSmallInteger('required_level')->default(1);
            $table->unsignedSmallInteger('current_level')->default(0);
            $table->smallInteger('gap_size')->default(0);
            $table->string('priority')->default('moderate');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gap_assessment_items');
        Schema::dropIfExists('gap_assessments');
    }
};
