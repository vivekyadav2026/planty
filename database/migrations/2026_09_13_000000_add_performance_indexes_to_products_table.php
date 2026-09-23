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
        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'is_bestseller'], 'idx_products_active_bestseller');
            $table->index(['is_active', 'is_featured'], 'idx_products_active_featured');
            $table->index(['is_active', 'deal_of_week'], 'idx_products_active_deal');
            $table->index(['is_active', 'category_id'], 'idx_products_active_cat');
            $table->index(['is_active', 'created_at'], 'idx_products_active_latest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_active_bestseller');
            $table->dropIndex('idx_products_active_featured');
            $table->dropIndex('idx_products_active_deal');
            $table->dropIndex('idx_products_active_cat');
            $table->dropIndex('idx_products_active_latest');
        });
    }
};
