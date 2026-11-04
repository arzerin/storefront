<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('original_name');
            $table->string('alt_text', 250);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->timestamps();
        });
        Schema::create('site_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('slug', 150)->unique();
            $table->string('template', 40)->default('editorial');
            $table->boolean('is_home')->default(false);
            $table->json('draft_sections');
            $table->unsignedBigInteger('published_revision_id')->nullable();
            $table->timestamps();
        });
        Schema::create('site_page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_page_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->string('template', 40);
            $table->json('sections');
            $table->timestamp('published_at');
            $table->timestamps();
        });
        Schema::table('site_blocks', function (Blueprint $table) {
            $table->foreignId('media_asset_id')->nullable()->after('body')->constrained('media_assets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('site_blocks', fn (Blueprint $table) => $table->dropConstrainedForeignId('media_asset_id'));
        Schema::dropIfExists('site_page_revisions');
        Schema::dropIfExists('site_pages');
        Schema::dropIfExists('media_assets');
    }
};
