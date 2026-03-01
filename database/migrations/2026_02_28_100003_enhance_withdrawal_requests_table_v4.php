<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->decimal('tax_amount', 15, 2)->default(0)->after('amount');
            $table->decimal('final_amount', 15, 2)->storedAs('amount - tax_amount')->after('tax_amount');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('final_amount');
            $table->foreignId('delegated_to')->nullable()->constrained('users')->cascadeOnDelete()->after('rejection_reason');
            $table->text('notes')->nullable()->after('delegated_to');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropColumn(['tax_amount', 'final_amount', 'tax_rate', 'delegated_to', 'notes']);
        });
    }
};
