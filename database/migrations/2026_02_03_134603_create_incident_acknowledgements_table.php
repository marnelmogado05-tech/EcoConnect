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
        Schema::create('incident_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->onDelete('cascade');
            $table->foreignId('officer_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('acknowledged_at')->nullable(); // When documentation was submitted
            $table->text('documentation')->nullable(); // The documentation notes
            $table->integer('evidence_count')->default(0); // Number of evidence files
            $table->timestamps();

            $table->unique(['incident_id', 'officer_id']);
            $table->index(['incident_id', 'officer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_acknowledgements');
    }
};
