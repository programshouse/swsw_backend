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
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
              $table->morphs('notifiable');

            $table->string('type');

            $table->string('title_ar');
            $table->string('title_en');

            $table->text('body_ar');
            $table->text('body_en');

            $table->json('data')->nullable();

            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index([
                'notifiable_type',
                'notifiable_id',
                'read_at',
            ]);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
