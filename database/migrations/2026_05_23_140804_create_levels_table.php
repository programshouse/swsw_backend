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
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // الحد الأدنى للكاش أو المستهدف
            $table->decimal('cash_money', 10, 2)->default(0);

            // عدد الكيلومترات
            $table->integer('km')->default(0);

            // وسيلة المواصلات
           $table->foreignId('vehicle_id')
        ->nullable()
        ->constrained('vehicles')
        ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
