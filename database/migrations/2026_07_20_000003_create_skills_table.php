<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('category')->nullable();
            $table->string('taxonomy_source')->nullable();
            $table->json('embedding')->nullable();
            $table->timestamps();
        });

        Schema::create('skill_aliases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('skill_id');
            $table->foreign('skill_id')->references('id')->on('skills')->cascadeOnDelete();
            $table->string('alias');
            $table->unique(['skill_id', 'alias']);
        });

        Schema::create('user_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('profile_id');
            $table->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->uuid('skill_id');
            $table->foreign('skill_id')->references('id')->on('skills')->cascadeOnDelete();
            $table->unsignedSmallInteger('proficiency_level')->default(1);
            $table->string('source')->default('self_reported');
            $table->uuid('evidence_ref')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->date('last_used_date')->nullable();
            $table->timestamps();
            $table->unique(['profile_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_skills');
        Schema::dropIfExists('skill_aliases');
        Schema::dropIfExists('skills');
    }
};
