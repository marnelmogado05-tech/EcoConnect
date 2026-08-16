<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The incidents table shipped with two defects that made ordinary actions impossible:
 *
 *   - priority was enum('Normal','High','Urgend') — a typo. Every part of the
 *     application writes 'Urgent', which strict mode rejects outright.
 *   - status was enum('Pending','In Progress','Resolved','Rejected'), but assigning an
 *     incident writes 'Assigned', and four dashboards count on that value.
 *
 * Both columns become plain strings rather than corrected enums. The enum is what caused
 * the failures: every new value needs a schema migration, and the two databases in play
 * disagree about how strictly they enforce it. M3 reintroduces the constraint where it
 * belongs, as PHP backed enums cast on the model.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->string('incident_type', 50)->change();
            $table->string('status', 20)->default('Pending')->change();
            $table->string('priority', 20)->default('Normal')->change();
        });

        // Only safe once the column no longer rejects the corrected spelling.
        DB::table('incidents')->where('priority', 'Urgend')->update(['priority' => 'Urgent']);
    }

    /**
     * Reverse the migrations.
     *
     * Restores the original definitions, typo included — a reversal should put the schema
     * back as it was, not silently keep half of the fix.
     */
    public function down(): void
    {
        DB::table('incidents')->where('priority', 'Urgent')->update(['priority' => 'Urgend']);
        DB::table('incidents')->where('status', 'Assigned')->update(['status' => 'In Progress']);

        Schema::table('incidents', function (Blueprint $table) {
            $table->enum('incident_type', [
                'Illegal Logging', 'Pollution', 'Wildlife Crime', 'Illegal Waste Disposal', 'Other',
            ])->change();
            $table->enum('status', ['Pending', 'In Progress', 'Resolved', 'Rejected'])
                ->default('Pending')->change();
            $table->enum('priority', ['Normal', 'High', 'Urgend'])->default('Normal')->change();
        });
    }
};
