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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('kitchen_id')->constrained('kitchen_profiles')->onDelete('cascade');
            $table->decimal('total', 10, 2);
            $table->unsignedBigInteger('address_id')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'assigned','picked_up','delivered', 'cancelled', 'ready_to_deliver', 'preparing', 'received_by_delivery'])->default('pending');
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
