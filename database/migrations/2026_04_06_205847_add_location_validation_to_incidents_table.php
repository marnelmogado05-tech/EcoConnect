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
        Schema::table('incidents', function (Blueprint $table) {
            $table->boolean('location_validated')->default(false)->comment('Location validation via Nominatim API');
            $table->boolean('location_is_valid')->nullable()->comment('Whether location is in allowed municipalities');
            $table->json('validated_locations')->nullable()->comment('Validated location data from photos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn(['location_validated', 'location_is_valid', 'validated_locations']);
        });
    }
};
