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
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // VARCHAR rather than TEXT. The composite unique below leads on user_id, so
            // InnoDB drops the foreign key's own index as redundant and expects the new
            // index to support the constraint instead — which a TEXT column cannot do
            // without a prefix length. As TEXT, this table cannot be created on MySQL or
            // MariaDB at all (errno 150). 700 characters clears the longest web-push
            // subscription payloads while (8 + 700*4) stays under InnoDB's 3072-byte key
            // limit. The column holds a subscription JSON document, not an FCM token;
            // both it and the table name are corrected in M3.
            $table->string('token', 700);
            $table->string('device_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'token']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
