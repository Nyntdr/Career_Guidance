<?php

use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->uuid('category_id')->nullable()->after('name');
            $table->foreign('category_id')->references('id')->on('skill_categories')->nullOnDelete();
        });

        $distinct = Skill::select('category')->distinct()->whereNotNull('category')->pluck('category');
        foreach ($distinct as $cat) {
            $category = SkillCategory::create([
                'name' => $cat,
                'slug' => Str::slug($cat),
            ]);
            Skill::where('category', $cat)->update(['category_id' => $category->id]);
        }

        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
        });

        Skill::with('category')->each(function ($skill) {
            if ($skill->category) {
                $skill->category = $skill->category->name;
                $skill->save();
            }
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('skill_categories');
    }
};
