<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // The application stores name parts separately; there is no single `name`
            // column. Anything reading $user->name relies on an accessor, not a column.
            $table->string('fname');
            $table->string('mname')->nullable();
            $table->string('lname');
            $table->string('extname', 10)->nullable();

            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();

            // Deliberately plain strings rather than enums. The application currently
            // writes 'active', 'Active' and 'Suspended' from different call sites, and a
            // DB enum would reject them under strict mode. These become PHP backed enums
            // in M3; until then the column must accept what the code actually writes.
            $table->string('role', 20)->default('user')->index();
            $table->string('status', 20)->default('Active')->index();

            // Constrained in 2025_10_02_064400, once municipalities and barangays exist.
            $table->foreignId('municipality_id')->nullable();
            $table->foreignId('barangay_id')->nullable();

            // Raw bytes of the uploaded ID image. Widened to MEDIUMBLOB below: MySQL's
            // BLOB holds 64 KB while the upload rules accept 5 MB, so a plain binary
            // column would silently truncate every ID card. Moves to file storage in M2.
            $table->binary('id_card')->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `users` MODIFY `id_card` MEDIUMBLOB NULL');
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
