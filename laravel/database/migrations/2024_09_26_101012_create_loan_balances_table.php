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
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_balances');
    }
};
