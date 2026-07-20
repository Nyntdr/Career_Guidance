<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->unique();
            $table->text('description')->nullable();
            $table->string('typical_seniority')->nullable();
            $table->json('avg_salary_range')->nullable();
            $table->string('growth_outlook')->nullable();
            $table->timestamps();
        });

        Schema::create('career_role_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('career_role_id');
            $table->foreign('career_role_id')->references('id')->on('career_roles')->cascadeOnDelete();
            $table->uuid('skill_id');
            $table->foreign('skill_id')->references('id')->on('skills')->cascadeOnDelete();
            $table->string('importance')->default('required');
            $table->unsignedSmallInteger('min_proficiency')->default(1);
            $table->timestamps();
            $table->unique(['career_role_id', 'skill_id']);
        });

        Schema::create('career_paths', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('from_role_id');
            $table->foreign('from_role_id')->references('id')->on('career_roles')->cascadeOnDelete();
            $table->uuid('to_role_id');
            $table->foreign('to_role_id')->references('id')->on('career_roles')->cascadeOnDelete();
            $table->unsignedInteger('typical_years')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['from_role_id', 'to_role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_paths');
        Schema::dropIfExists('career_role_skills');
        Schema::dropIfExists('career_roles');
    }
};
