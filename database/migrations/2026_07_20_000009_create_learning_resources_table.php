<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('provider')->nullable();
            $table->string('url')->nullable();
            $table->string('resource_type')->default('course');
            $table->decimal('duration_hours', 8, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('learning_resource_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('learning_resource_id');
            $table->foreign('learning_resource_id')->references('id')->on('learning_resources')->cascadeOnDelete();
            $table->uuid('skill_id');
            $table->foreign('skill_id')->references('id')->on('skills')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['learning_resource_id', 'skill_id']);
        });

        Schema::create('gap_recommendations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('gap_assessment_item_id');
            $table->foreign('gap_assessment_item_id')->references('id')->on('gap_assessment_items')->cascadeOnDelete();
            $table->uuid('learning_resource_id');
            $table->foreign('learning_resource_id')->references('id')->on('learning_resources')->cascadeOnDelete();
            $table->unsignedInteger('rank')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gap_recommendations');
        Schema::dropIfExists('learning_resource_skills');
        Schema::dropIfExists('learning_resources');
    }
};
