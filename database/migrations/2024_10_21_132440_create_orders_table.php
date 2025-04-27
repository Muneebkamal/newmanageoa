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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->nullable();
            $table->string('source')->nullable();
            $table->string('destination')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('date')->nullable();
            $table->string('card_used')->nullable();
            $table->string('cash_back_source')->nullable();
            $table->string('cash_back_percentage')->nullable();
            $table->text('note')->nullable();
            $table->double('pre_tax_discount')->default(0);
            $table->double('post_tax_discount')->default(0);
            $table->double('shipping_cost')->default(0);
            $table->double('sales_tax')->default(0);
            $table->double('is_sale_tax_shipping')->default(0);
            $table->double('total')->default(0);
            $table->double('subtotal')->default(0);
            $table->double('sales_tax_rate')->default(0);
            $table->integer('total_units_purchased')->default(0);
            $table->integer('total_units_received')->default(0);
            $table->integer('total_units_shipped')->default(0);
            $table->integer('unit_errors')->default(0);
            $table->decimal('tax_paid', 10, 2)->default(0.00); // For monetary values
            $table->decimal('tax_percent', 5, 2)->default(0.00); // For percentages
            $table->integer('created_by')->default(0);
            $table->integer('buyer_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
