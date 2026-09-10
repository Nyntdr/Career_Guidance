<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_recommendations', function (Blueprint $table) {
            $table->string('rationale_source', 20)->default('template')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('career_recommendations', function (Blueprint $table) {
            $table->dropColumn('rationale_source');
        });
    }
};