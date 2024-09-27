<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_balances', function (Blueprint $table) {
            $table->softDeletes();
            $table->uuid('id')->primary();
            $table->integer('month');
            $table->integer('balance');
            $table->integer('deposit');
            $table->unsignedBigInteger('loan_account_id');
            $table->string('currency')->default('AUD');
            $table->string('scenario')->default('normal');
            $table->integer('offset_amount')->nullable();
            $table->integer('credit_card_amount')->nullable();
            $table->integer('offset_balance')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_balances');
    }
};
