<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('headline')->nullable();
            $table->string('current_title')->nullable();
            $table->unsignedInteger('years_experience')->nullable();
            $table->string('location')->nullable();
            $table->json('target_roles')->nullable();
            $table->json('target_industries')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        Schema::create('education', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('profile_id');
            $table->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->string('institution');
            $table->string('degree')->nullable();
            $table->string('field_of_study')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('work_experience', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('profile_id');
            $table->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->string('company');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_experience');
        Schema::dropIfExists('education');
        Schema::dropIfExists('profiles');
    }
};
