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
        Schema::create('delivery_shift_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_user_id')->constrained()->cascadeOnDelete();

            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
             $table->decimal('start_lat', 10, 7)->nullable()->after('delivery_user_id');
        $table->decimal('start_lng', 10, 7)->nullable()->after('start_lat');

        $table->decimal('end_lat', 10, 7)->nullable()->after('end_time');
        $table->decimal('end_lng', 10, 7)->nullable()->after('end_lat');

            $table->enum('status', ['active', 'finished'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_shift_logs');
    }
};
