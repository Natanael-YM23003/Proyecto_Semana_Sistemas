<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            if (! Schema::hasColumn('evidences', 'title')) {
                $table->string('title', 255)->default('Evidencia')->after('id');
            }
            if (! Schema::hasColumn('evidences', 'event_reference')) {
                $table->string('event_reference', 255)->nullable()->after('title');
            }
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE evidences ALTER COLUMN post_id DROP NOT NULL');
            DB::statement('ALTER TABLE evidences ALTER COLUMN user_id DROP NOT NULL');
            DB::statement('ALTER TABLE evidences DROP CONSTRAINT IF EXISTS evidences_post_id_user_id_unique');
        } else {
            try {
                Schema::table('evidences', function (Blueprint $table) {
                    $table->foreignId('post_id')->nullable()->change();
                    $table->foreignId('user_id')->nullable()->change();
                });
            } catch (\Throwable $e) {
            }
        }
    }

    public function down(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            if (Schema::hasColumn('evidences', 'event_reference')) {
                $table->dropColumn('event_reference');
            }
            if (Schema::hasColumn('evidences', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};
