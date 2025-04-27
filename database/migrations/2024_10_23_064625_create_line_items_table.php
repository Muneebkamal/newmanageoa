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
        Schema::create('line_items', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->nullable();
            $table->boolean('is_buylist')->default(0);
            $table->integer('buylist_id')->nullable();
            $table->string('name')->nullable();
            $table->string('asin')->nullable();
            $table->double('buy_cost')->default(0);
            $table->double('sku_total')->default(0);
            $table->integer('unit_purchased')->default(0);
            $table->text('product_buyer_notes')->nullable();
            $table->text('upc')->nullable();
            $table->double('list_price')->default(0);
            $table->double('min')->default(0);
            $table->double('max')->default(0);
            $table->string('category')->nullable();
            $table->string('supplier')->nullable();
            $table->longText('source_url')->nullable();
            $table->string('order_note')->nullable();
            $table->string('selling_price')->nullable();
            $table->double('net_profit')->nullable();
            $table->string('bsr')->nullable();
            $table->boolean('is_hazmat')->default(0);
            $table->boolean('is_disputed')->default(0);
            $table->text('msku')->nullable();
            $table->text('promo')->nullable();
            $table->text('coupon_code')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_rejected')->default(0);
            $table->double('sales_tax_rate')->default(0);
            $table->integer('total_units_purchased')->default(0);
            $table->integer('total_units_received')->default(0);
            $table->integer('total_units_shipped')->default(0);
            $table->integer('unit_errors')->default(0);
            $table->decimal('tax_paid', 10, 2)->default(0.00); // For monetary values
            $table->decimal('tax_percent', 5, 2)->default(0.00); // For percentages
            $table->decimal('shipping_tax', 5, 2)->default(0.00); // For percentages
            $table->decimal('pre_discount', 5, 2)->default(0.00); // For percentages
            $table->decimal('post_discount', 5, 2)->default(0.00); // For percentages
            $table->integer('created_by')->nullable(); // For percentages
            $table->boolean('is_approved')->default(false); // 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('line_items');
    }
};
