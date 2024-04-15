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
        Schema::create('clients', function (Blueprint $table) {
            $table->softDeletes();
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->default('');
            $table->string('preferred_name')->default('');
            $table->string('email');
            $table->string('home_phone')->default('');
            $table->string('work_phone')->default('');
            $table->string('mobile_phone')->default('');
            $table->string('fax')->default('');
            $table->uuid('user_id');
            $table->uuid('physical_address_id')->nullable();
            $table->uuid('postal_address_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
