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
            $table->string('email')->nullable()->after('client_phone');
            $table->foreignId('referral_link_id')->nullable()->constrained()->nullOnDelete()->after('user_id');
            $table->string('product_interest')->nullable()->after('company_name');
            $table->string('service_type')->nullable()->after('product_interest');
            $table->decimal('expected_deal_value', 15, 2)->nullable()->after('service_type');
            $table->string('source')->default('direct')->after('expected_deal_value'); // direct, referral, campaign, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['referral_link_id']);
            $table->dropColumn(['email', 'referral_link_id', 'product_interest', 'service_type', 'expected_deal_value', 'source']);
        });
    }
};
