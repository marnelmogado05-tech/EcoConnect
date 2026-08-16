<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The users table is created first (Laravel's 0001_01_01 convention, and the sessions
 * table depends on it), but its location columns point at municipalities and barangays,
 * which are created later. The constraints are therefore added here rather than inline,
 * so no table has to be created twice to satisfy ordering.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite cannot add foreign key constraints to an existing table; the columns
        // and application-level validation still apply there.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('municipality_id')->references('id')->on('municipalities')->nullOnDelete();
            $table->foreign('barangay_id')->references('id')->on('barangays')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['municipality_id']);
            $table->dropForeign(['barangay_id']);
        });
    }
};
