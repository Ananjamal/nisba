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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('commission_type')->default('fixed')->after('status'); // fixed, percentage
            $table->decimal('commission_rate', 10, 2)->nullable()->after('commission_type');
            $table->string('sector')->nullable()->after('commission_rate');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('sector')->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['commission_type', 'commission_rate', 'sector']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sector');
        });
    }
};
