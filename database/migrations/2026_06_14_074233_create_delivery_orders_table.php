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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();


            $table->foreignId('delivery_user_id')
                ->nullable()
                ->constrained('delivery_users')
                ->nullOnDelete();

            $table->enum('status', ['accepted', 'picked_up', 'on_the_way','cancelled_by_client','delivered','rejected'])->nullable();

            $table->timestamp('rejected_at')->nullable();

              $table->boolean('cash_settled')
            ->default(false);
           

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
