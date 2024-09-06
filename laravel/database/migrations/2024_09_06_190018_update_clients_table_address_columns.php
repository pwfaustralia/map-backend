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
        Schema::table('clients', function (Blueprint $table) {
            // Remove the physical_address_id and postal_address_id columns
            $table->dropColumn('physical_address_id');
            $table->dropColumn('postal_address_id');

            // Add new address columns
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Add the removed columns back
            $table->uuid('physical_address_id')->nullable();
            $table->uuid('postal_address_id')->nullable();

            // Remove the newly added address columns
            $table->dropColumn('address_1');
            $table->dropColumn('address_2');
            $table->dropColumn('city');
            $table->dropColumn('postcode');
            $table->dropColumn('state');
            $table->dropColumn('country');
        });
    }
};
