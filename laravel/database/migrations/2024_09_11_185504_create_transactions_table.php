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
        Schema::create('transactions', function (Blueprint $table) {
            $table->softDeletes();
            $table->uuid('id')->primary();
            $table->string('container')->default('bank');
            $table->unsignedBigInteger('transaction_id')->unique();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('category_type')->default('UNCATEGORIZE');
            $table->unsignedBigInteger('category_id');
            $table->string('base_type')->nullable();
            $table->string('category')->default('Uncategorised');
            $table->string('category_source')->default('SYSTEM');
            $table->unsignedBigInteger('high_level_category_id');
            $table->timestamp('created_date')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->string('source_type')->default('AGGREGATED');
            $table->date('transaction_date')->nullable();
            $table->date('post_date')->nullable();
            $table->string('status')->default('POSTED');
            $table->unsignedBigInteger('account_id');
            $table->decimal('running_balance', 15, 2)->nullable();
            $table->string('check_number')->nullable();
            $table->uuid('batch_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
