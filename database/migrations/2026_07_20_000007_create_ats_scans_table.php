<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ats_scans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('resume_id');
            $table->foreign('resume_id')->references('id')->on('resumes')->cascadeOnDelete();
            $table->uuid('job_posting_id')->nullable();
            $table->foreign('job_posting_id')->references('id')->on('job_postings')->nullOnDelete();
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->decimal('keyword_match_score', 5, 2)->nullable();
            $table->decimal('formatting_score', 5, 2)->nullable();
            $table->decimal('structure_score', 5, 2)->nullable();
            $table->decimal('readability_score', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('ats_scan_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ats_scan_id');
            $table->foreign('ats_scan_id')->references('id')->on('ats_scans')->cascadeOnDelete();
            $table->string('category');
            $table->text('detail')->nullable();
            $table->string('severity')->default('low');
            $table->text('suggestion')->nullable();
            $table->timestamps();
        });

        Schema::create('ats_matched_keywords', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ats_scan_id');
            $table->foreign('ats_scan_id')->references('id')->on('ats_scans')->cascadeOnDelete();
            $table->string('keyword');
            $table->boolean('matched')->default(false);
            $table->unsignedInteger('frequency_in_resume')->default(0);
            $table->unsignedInteger('frequency_in_jd')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ats_matched_keywords');
        Schema::dropIfExists('ats_scan_details');
        Schema::dropIfExists('ats_scans');
    }
};
