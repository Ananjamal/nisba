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
            $table->unsignedBigInteger('finance_approved_by')->nullable()->after('status');
            $table->timestamp('finance_approved_at')->nullable()->after('finance_approved_by');
            $table->unsignedBigInteger('admin_approved_by')->nullable()->after('finance_approved_at');
            $table->timestamp('admin_approved_at')->nullable()->after('admin_approved_by');
            $table->string('rejection_reason')->nullable()->after('admin_approved_at');
            $table->string('payment_method')->nullable()->after('rejection_reason');

            $table->foreign('finance_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('admin_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropForeign(['finance_approved_by']);
            $table->dropForeign(['admin_approved_by']);
            $table->dropColumn([
                'finance_approved_by',
                'finance_approved_at',
                'admin_approved_by',
                'admin_approved_at',
                'rejection_reason',
                'payment_method'
            ]);
        });
    }
};
