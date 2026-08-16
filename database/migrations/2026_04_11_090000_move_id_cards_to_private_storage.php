<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Replaces the id_card BLOB column with a path into private storage.
 *
 * Holding a government ID inline on the users row meant that every `select *` on users
 * — an admin listing, a queued job payload, a session flash — dragged megabytes of
 * identity documents with it, and any accidental serialisation of the model exposed
 * them. The bytes move to the private local disk; the row keeps only a path, and the
 * files are served through a policy-checked route.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_card_path')->nullable()->after('barangay_id');
        });

        $this->moveExistingCardsToDisk();

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id_card');
        });
    }

    /**
     * Reverse the migrations.
     *
     * The image bytes are not read back out of storage: reversing this puts the column
     * shape back, not the contents. The files remain on disk under id-cards/.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->binary('id_card')->nullable()->after('barangay_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `users` MODIFY `id_card` MEDIUMBLOB NULL');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id_card_path');
        });
    }

    /**
     * Write any stored BLOBs out as files so no ID card is lost.
     */
    private function moveExistingCardsToDisk(): void
    {
        DB::table('users')
            ->select('id', 'id_card')
            ->whereNotNull('id_card')
            ->orderBy('id')
            ->chunk(50, function ($users) {
                foreach ($users as $user) {
                    if (blank($user->id_card)) {
                        continue;
                    }

                    $path = 'id-cards/'.Str::ulid().'.jpg';

                    Storage::disk('local')->put($path, $user->id_card);

                    DB::table('users')->where('id', $user->id)->update(['id_card_path' => $path]);
                }
            });
    }
};
