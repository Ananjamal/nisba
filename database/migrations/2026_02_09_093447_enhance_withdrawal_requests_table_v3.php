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
            $table->foreignId('lead_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('client_name')->nullable()->after('amount');
            $table->string('company_name')->nullable()->after('client_name');
            $table->string('invoice_url')->nullable()->after('status');
            $table->string('iban_proof_url')->nullable()->after('invoice_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->dropColumn(['lead_id', 'client_name', 'company_name', 'invoice_url', 'iban_proof_url']);
        });
    }
};
