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
        Schema::create('rate_stores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();

            $table->foreignId('delivery_user_id')
                ->nullable()
                ->constrained('delivery_users')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('user_rate_id')
                ->nullable()
                ->constrained('user_rates')
                ->nullOnDelete();

            $table->enum('rater_type', ['client', 'kitchen', 'delivery']);
            $table->enum('rated_type', ['client', 'kitchen', 'delivery']);

            $table->unsignedTinyInteger('score');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_stores');
    }
};
