<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('title', 150);
            $table->string('slug', 180)->unique();
            $table->date('activity_date');
            $table->string('excerpt', 300)->nullable();
            $table->longText('content');
            $table->string('cover_image', 255)->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
