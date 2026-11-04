<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        Schema::create('site_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('body')->nullable();
            $table->string('region', 40);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->index(['region', 'is_enabled', 'sort_order']);
        });
        Schema::create('footer_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('section', 40);
            $table->string('label', 100);
            $table->string('url', 500);
            $table->string('audience', 20)->default('all');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->index(['section', 'is_enabled', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_menu_items');
        Schema::dropIfExists('site_blocks');
        Schema::dropIfExists('site_settings');
    }
};
