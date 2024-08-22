<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function ($table) {
            $table->string('yodlee_username')->default('');
            $table->string('yodlee_status')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function ($table) {
            $table->dropColumn('yodlee_username');
            $table->dropColumn('yodlee_status');
        });
    }
};
