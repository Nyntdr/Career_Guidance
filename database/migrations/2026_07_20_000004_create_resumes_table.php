<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('profile_id');
            $table->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->string('file_url');
            $table->string('file_type')->nullable();
            $table->text('raw_text')->nullable();
            $table->json('parsed_json')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_primary')->default(false);
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('resume_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('resume_id');
            $table->foreign('resume_id')->references('id')->on('resumes')->cascadeOnDelete();
            $table->string('section_type');
            $table->text('content')->nullable();
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_sections');
        Schema::dropIfExists('resumes');
    }
};
