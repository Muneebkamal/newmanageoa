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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->integer('source_id')->nullable();
            $table->boolean('bundle')->default(0);
            $table->date('date')->nullable();
            $table->string('name')->nullable();
            $table->string('asin')->nullable();
            $table->longText('url')->nullable();
            $table->string('supplier')->nullable();
            $table->double('cost')->default(0);
            $table->double('sell_price')->default(0);
            $table->double('net_profit')->default(0);
            $table->string('roi')->nullable();
            $table->string('bsr')->nullable();
            $table->string('category')->nullable();
            $table->string('promo')->nullable();
            $table->text('notes')->nullable();
            $table->string('currency')->nullable();
            $table->string('coupon')->nullable();
            $table->boolean('is_hazmat')->default(0);
            $table->boolean('is_disputed')->default(0);
            $table->text('tags')->nullable();
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
