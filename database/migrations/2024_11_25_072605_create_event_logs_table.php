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
        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->nullable();
            $table->integer('order_item_id')->nullable();
            $table->string('issue_type')->nullable();
            $table->string('status')->nullable();
            $table->boolean('cancelled')->default(false);
            $table->boolean('cc_charged')->default(false);
            $table->boolean('refunded')->default(false);
            $table->boolean('received')->default(false);
            $table->text('issue_notes')->nullable();
            $table->integer('item_quantity')->default(0);
            $table->double('refund_actual')->default(0);
            $table->double('refund_expected')->default(0);
            $table->text('supplier_notes')->nullable();
            $table->text('tracking_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_logs');
    }
};
