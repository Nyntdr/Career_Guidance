<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source')->default('internal');
            $table->string('external_id')->nullable();
            $table->string('title');
            $table->string('company')->nullable();
            $table->text('raw_description')->nullable();
            $table->json('parsed_requirements')->nullable();
            $table->string('seniority_level')->nullable();
            $table->string('location')->nullable();
            $table->json('embedding')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('job_required_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_posting_id');
            $table->foreign('job_posting_id')->references('id')->on('job_postings')->cascadeOnDelete();
            $table->uuid('skill_id');
            $table->foreign('skill_id')->references('id')->on('skills')->cascadeOnDelete();
            $table->string('importance')->default('required');
            $table->unsignedSmallInteger('min_proficiency')->default(1);
            $table->timestamps();
            $table->unique(['job_posting_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_required_skills');
        Schema::dropIfExists('job_postings');
    }
};
