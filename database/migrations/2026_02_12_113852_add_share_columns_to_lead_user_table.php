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
        Schema::table('lead_user', function (Blueprint $table) {
            $table->decimal('commission_share', 5, 2)->nullable()->comment('Percentage share (0-100)');
            $table->decimal('fixed_amount', 12, 2)->nullable()->comment('Override with fixed amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_user', function (Blueprint $table) {
            $table->dropColumn(['commission_share', 'fixed_amount']);
        });
    }
};
