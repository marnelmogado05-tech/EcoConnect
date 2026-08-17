<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stores where an incident happened, instead of asking OpenStreetMap on every page view.
 *
 * The admin incident list, the municipality dropdown and the analytics page each
 * reverse-geocoded coordinates inline while rendering, with deliberate sleeps between
 * calls to respect the Nominatim rate limit — one second per distinct coordinate pair in
 * the dropdown, 200ms per incident elsewhere. A few hundred incidents made those pages
 * take minutes.
 *
 * Location is now resolved once by a queued job and written here, so filtering by
 * municipality is an indexed WHERE clause.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->foreignId('municipality_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();

            $table->string('municipality_name')->nullable()->after('municipality_id');
            $table->string('address')->nullable()->after('municipality_name');

            // The filter columns on every list page, none of which were indexed.
            $table->index('user_id');
            $table->index('priority');
            $table->index('incident_type');
        });

        Schema::table('media_evidence', function (Blueprint $table) {
            // Resolved alongside the incident so the address accessor stops making an
            // uncached HTTP request every time a template touches it.
            $table->string('address')->nullable()->after('longitude');
            $table->timestamp('geocoded_at')->nullable()->after('address');
        });

        Schema::table('users', function (Blueprint $table) {
            // role and status are filtered on every admin page.
            $table->index(['role', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'status']);
        });

        Schema::table('media_evidence', function (Blueprint $table) {
            $table->dropColumn(['address', 'geocoded_at']);
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['incident_type']);

            if (DB::getDriverName() !== 'sqlite') {
                $table->dropConstrainedForeignId('municipality_id');
            } else {
                $table->dropColumn('municipality_id');
            }

            $table->dropColumn(['municipality_name', 'address']);
        });
    }
};
