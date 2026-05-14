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
        Schema::table('wallet_debit_requests', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->enum('payment_method' , ['instapay' , 'vodafone_cash' , 'etisalat_cash', 'orange_cash' , 'bank_transfer']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_debit_requests', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('payment_method');
        });
    }
};
