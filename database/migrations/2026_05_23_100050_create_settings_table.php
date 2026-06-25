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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->text('whatsapp_number')->nullable();
            $table->text('facebook_link')->nullable();
            $table->text('instgram_link')->nullable();
            $table->text('tiktok_link')->nullable();
              $table->string('user_video')->nullable();
    $table->string('kitchen_video')->nullable();
    $table->string('delivery_video')->nullable();
          
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
