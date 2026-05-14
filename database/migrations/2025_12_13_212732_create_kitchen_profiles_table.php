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
        Schema::create('kitchen_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('work_day_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('government_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('logo');
            $table->string('cover');
            $table->string('name');
            $table->string('phone')->unique()->nullable();
            $table->string('whatsapp')->unique()->nullable();
            $table->string('facebook')->nullable();
            $table->string('location')->nullable();
            $table->boolean('have_star')->default(false);
            $table->enum('statue', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('open_status', ['open', 'closed', 'busy'])->default('closed');
            $table->text('rejected_note')->nullable();
            $table->time('working_time_start');
            $table->time('working_time_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_profiles');
    }
};
