<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('expired')->default('false');
            $table->timestamp('logged_in_at')->nullable();
            $table->timestamp('last_visited_at')->nullable();
            $table->boolean('override_role')->default('false');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('expired');
            $table->dropColumn('logged_in_at');
            $table->dropColumn('last_visited_at');
            $table->dropColumn('override_role');
        });
    }
};
