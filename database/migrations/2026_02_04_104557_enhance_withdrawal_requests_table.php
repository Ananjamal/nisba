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
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->string('iban')->nullable()->after('amount');
            $table->string('bank_name')->nullable()->after('iban');
            $table->string('account_holder_name')->nullable()->after('bank_name');
            $table->string('payment_proof_url')->nullable()->after('bank_details');
            $table->text('admin_notes')->nullable()->after('payment_proof_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            //
        });
    }
};
