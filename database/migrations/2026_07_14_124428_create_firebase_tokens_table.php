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
        Schema::create('firebase_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');

            $table->text('token')->unique();

            $table->enum('app_type', [
                'client',
                'kitchen',
                'delivery',
            ]);

            $table->enum('language', [
                'ar',
                'en',
            ])->default('ar');

            $table->string('device_id')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_name')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();

            $table->index([
                'app_type',
                'is_active',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firebase_tokens');
    }
};
