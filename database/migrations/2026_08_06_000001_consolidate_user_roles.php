<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'candidate')->update(['role' => 'user']);
        DB::table('users')->where('role', 'counselor')->update(['role' => 'user']);
    }

    public function down(): void
    {
        // Not reversible; role values were consolidated to 'user'.
    }
};
