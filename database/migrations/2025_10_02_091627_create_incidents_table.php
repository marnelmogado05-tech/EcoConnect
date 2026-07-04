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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // Short summary of the incident
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('incident_type', ['Illegal Logging', 'Pollution', 'Wildlife Crime', 'Illegal Waste Disposal', 'Other']);
            $table->text('description');
            $table->date('incident_date');
            $table->time('incident_time');
            $table->enum('status', ['Pending', 'In Progress', 'Resolved', 'Rejected'])->default('Pending');
            $table->enum('priority', ['Normal', 'High', 'Urgend'])->default('Normal');
            $table->text('rejection_reason')->nullable();
            $table->text('resolution_details')->nullable();
            $table->date('resolved_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->date('assigned_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('incident_date');
            $table->index('assigned_at');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
