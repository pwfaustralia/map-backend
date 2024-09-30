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
        Schema::create('accounts', function (Blueprint $table) {
            $table->softDeletes();
            $table->uuid('id')->primary();
            $table->string('container');
            $table->boolean('is_primary')->default(false);
            $table->unsignedBigInteger('account_id')->unique();
            $table->uuid('client_id')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->uuid('batch_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
