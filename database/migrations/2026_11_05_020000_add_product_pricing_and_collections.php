<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('sale_price')->nullable();
            $table->timestamp('sale_starts_at')->nullable();
            $table->timestamp('sale_ends_at')->nullable();
            $table->string('discount_mode', 20)->default('combine');
        });

        Schema::create('quantity_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('min_quantity');
            $table->string('type', 20);
            $table->decimal('value', 10, 2);
            $table->timestamps();
            $table->unique(['product_id', 'min_quantity']);
        });

        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('description', 500)->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('collection_product', function (Blueprint $table) {
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['collection_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_product');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('quantity_discounts');
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['sale_price', 'sale_starts_at', 'sale_ends_at', 'discount_mode']));
    }
};
