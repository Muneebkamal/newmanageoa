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
        Schema::create('new_leads', function (Blueprint $table) {
            $table->id();
             /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    $table->unsignedBigInteger('source_id')->nullable();
    $table->unsignedBigInteger('created_by')->nullable();

    /*
    |--------------------------------------------------------------------------
    | BASIC
    |--------------------------------------------------------------------------
    */

    $table->date('date')->nullable();

    $table->text('image')->nullable();

    $table->text('name')->nullable();

    $table->string('supplier')->nullable();

    $table->string('category')->nullable();

    /*
    |--------------------------------------------------------------------------
    | URLS
    |--------------------------------------------------------------------------
    */

    $table->text('url')->nullable();

    $table->string('asin')->nullable();

    $table->text('dual_link_keepa')->nullable();

    /*
    |--------------------------------------------------------------------------
    | PRICES
    |--------------------------------------------------------------------------
    */

    $table->decimal('cost', 10, 2)->nullable();

    $table->decimal('sell_price', 10, 2)->nullable();

    $table->decimal('price_30_day', 10, 2)->nullable();

    $table->decimal('price_90_day', 10, 2)->nullable();

    $table->decimal('fba_fees', 10, 2)->nullable();

    $table->decimal('extra_costs', 10, 2)->nullable();

    /*
    |--------------------------------------------------------------------------
    | CHANGES
    |--------------------------------------------------------------------------
    */

    $table->decimal('change_30_day', 10, 2)->nullable();

    $table->decimal('change_90_day', 10, 2)->nullable();

    /*
    |--------------------------------------------------------------------------
    | PROFITS
    |--------------------------------------------------------------------------
    */

    $table->decimal('net_profit', 10, 2)->nullable();

    $table->decimal('roi', 10, 2)->nullable();

    /*
    |--------------------------------------------------------------------------
    | AMAZON DATA
    |--------------------------------------------------------------------------
    */

    $table->string('bsr')->nullable();

    $table->string('bsr_90_day')->nullable();

    $table->integer('sales_per_month')->nullable();

    $table->integer('fba_sellers')->nullable();

    $table->string('buy_box')->nullable();

    /*
    |--------------------------------------------------------------------------
    | BADGES / NOTES
    |--------------------------------------------------------------------------
    */

    $table->string('promo')->nullable();

    $table->text('notes')->nullable();

    /*
    |--------------------------------------------------------------------------
    | SHIPPING / CASHBACK
    |--------------------------------------------------------------------------
    */

    $table->string('shipping')->nullable();

    $table->decimal('cashback_percentage', 10, 2)->nullable();

    $table->decimal('giftcard_percentage', 10, 2)->nullable();

    /*
    |--------------------------------------------------------------------------
    | FLAGS
    |--------------------------------------------------------------------------
    */

    $table->boolean('is_hazmat')->default(false);

    $table->boolean('is_disputed')->default(false);

    $table->boolean('is_rejected')->default(false);

    /*
    |--------------------------------------------------------------------------
    | EXTRA
    |--------------------------------------------------------------------------
    */

    $table->text('reason')->nullable();

    /*
    |--------------------------------------------------------------------------
    | SOFT DELETE
    |--------------------------------------------------------------------------
    */

    $table->softDeletes();

    $table->timestamps();

    /*
    |--------------------------------------------------------------------------
    | FOREIGN KEYS
    |--------------------------------------------------------------------------
    */

    $table->foreign('source_id')
        ->references('id')
        ->on('new_sources')
        ->onDelete('cascade');

    $table->foreign('created_by')
        ->references('id')
        ->on('users')
        ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_leads');
    }
};
