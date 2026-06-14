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
            $table->enum('status', [
                'pending',
                'cancelled_by_kitchen',
                'accepted_by_kitchen',
                'preparing',
                'ready_to_deliver',
                'accepted_by_delivery',
                'received_by_delivery',
                'on_the_way',
                'cancelled_by_client',
                'cancelled_by_delivery',
                'delivered',
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
