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
        Schema::create('media_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->onDelete('cascade');
            $table->string('file_path'); // Store path to image file
            $table->string('file_name'); // Original file name
            $table->string('mime_type')->nullable(); // e.g., 'image/jpeg'
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->decimal('latitude', 10, 8)->nullable(); // Increased precision
            $table->decimal('longitude', 11, 8)->nullable(); // Increased precision
            $table->timestamps();   
            
            // Index for better performance
            $table->index('incident_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_evidence');
    }
};
