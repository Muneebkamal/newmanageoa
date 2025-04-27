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
        Schema::create('ship_events', function (Blueprint $table) {
            $table->id();
            $table->integer('shipping_batch')->nullable();
            $table->integer('order_id')->nullable();
            $table->integer('order_item_id')->nullable();
            $table->integer('items')->nullable();
            $table->text('qc_check')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('asin_override')->nullable();
            $table->string('product_name_override')->nullable();
            $table->double('min_orverride')->default(0);
            $table->double('list_price_orverride')->default(0);
            $table->double('max_orverride')->default(0);
            $table->string('condition')->nullable();
            $table->string('product_upc')->nullable();
            $table->string('msku_orderride')->nullable();
            $table->string('shipping_notes')->nullable();
            $table->boolean('description_matches_flag')->default(false);
            $table->boolean('image_matches_flag')->default(false);
            $table->boolean('title_matches_flag')->default(false);
            $table->boolean('upc_matches_flag')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ship_events');
    }
};
