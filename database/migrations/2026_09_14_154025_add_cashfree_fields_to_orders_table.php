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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('cashfree_order_id')->nullable()->after('payment_method');
            $table->string('cashfree_payment_id')->nullable()->after('cashfree_order_id');
            $table->text('cashfree_payment_session_id')->nullable()->after('cashfree_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cashfree_order_id', 'cashfree_payment_id', 'cashfree_payment_session_id']);
        });
    }
};
