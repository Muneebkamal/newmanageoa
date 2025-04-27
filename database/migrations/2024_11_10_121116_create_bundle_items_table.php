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
        Schema::create('bundle_items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('notes')->nullable();
            $table->string('supplier')->nullable();
            $table->longText('source_url')->nullable();
            $table->string('cost')->nullable();
            $table->string('coupon_code')->nullable();
            $table->string('promo')->nullable();
            $table->integer('item_id')->nullable();
            $table->boolean('is_buylist')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundle_items');
    }
};
