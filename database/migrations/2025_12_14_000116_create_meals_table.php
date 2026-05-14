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
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('kitchen_profile_id')->nullable()->constrained();
            $table->string('name') ;
            $table->text('description');
            $table->integer('quantity') ;
            $table->double('price') ;
            $table->string('image');
            $table->boolean('available_delivery_today') ;
            $table->integer('preparation_time') ;
            $table->text('recipe') ;
            $table->enum('approved' , ['approved' , 'rejected' , 'pending'])->default('pending') ;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
