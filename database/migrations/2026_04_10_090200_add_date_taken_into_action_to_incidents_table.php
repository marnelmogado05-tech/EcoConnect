<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `date_taken_into_action` was declared in Incident::$fillable, cast as a datetime on the
 * model, and written by both the police and BFP "take into action" handlers — but no
 * migration ever created it. Every attempt to take an incident into action failed on an
 * unknown column, was swallowed by the controller's catch block, and reported to the
 * officer as "Failed to take action on incident. Please try again."
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->timestamp('date_taken_into_action')
                ->nullable()
                ->after('assigned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('date_taken_into_action');
        });
    }
};
