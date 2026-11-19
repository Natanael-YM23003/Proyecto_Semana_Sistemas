<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('speaker', 150)->nullable()->after('excerpt');
            $table->text('learnings')->nullable()->after('content');
            $table->text('reflection')->nullable()->after('learnings');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['speaker', 'learnings', 'reflection']);
        });
    }
};
