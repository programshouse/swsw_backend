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
        Schema::create('pending_deliveries', function (Blueprint $table) {
            $table->id();
                   $table->foreignId('delivery_user_id')
                ->nullable()
                ->constrained('delivery_users')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->date('birthdate');

            $table->string('government_id');


            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();

            // type: company or freelance
            $table->enum('type', ['company', 'freelance']);

            // transport info
            $table->boolean('has_vehicle')->default(false);

            $table->foreignId('vehicle_id')
                ->nullable()
                ->constrained('vehicles')
                ->nullOnDelete();
            // image
            $table->string('image')->nullable();

            // vehicle type : car motorcycle bicycle 
            $table->enum('vehicle_type', ['car', 'motorcycle' ,'bicycle']);

            // status approval
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_deliveries');
    }
};
