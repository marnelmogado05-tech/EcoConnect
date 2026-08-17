<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The table never held FCM tokens.
 *
 * Its `token` column stores a browser web-push subscription — a JSON document with an
 * endpoint and a key pair — delivered through minishlink/web-push. The Firebase naming
 * came from two notification services that were built and never called, both of which
 * have now been deleted along with the kreait package.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('fcm_tokens', 'push_subscriptions');

        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->renameColumn('token', 'subscription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->renameColumn('subscription', 'token');
        });

        Schema::rename('push_subscriptions', 'fcm_tokens');
    }
};
