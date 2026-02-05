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
        Schema::table('users', function (Blueprint $table) {
            $table->string('iban')->nullable()->after('phone');
            $table->string('bank_name')->nullable()->after('iban');
            $table->string('account_holder_name')->nullable()->after('bank_name');
            $table->timestamp('bank_account_verified_at')->nullable()->after('account_holder_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['iban', 'bank_name', 'account_holder_name', 'bank_account_verified_at']);
        });
    }
};
