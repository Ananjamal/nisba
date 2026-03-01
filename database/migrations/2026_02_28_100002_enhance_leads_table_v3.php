<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('unique_id')->nullable()->unique()->after('id');
            $table->boolean('is_duplicate')->default(false)->after('unique_id');
            $table->text('duplicate_notes')->nullable()->after('is_duplicate');
            $table->foreignId('approved_by')->nullable()->constrained('users')->cascadeOnDelete()->after('duplicate_notes');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->date('subscription_renewal_date')->nullable()->after('approved_at');
            $table->date('renewal_notification_sent')->nullable()->after('subscription_renewal_date');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'unique_id',
                'is_duplicate',
                'duplicate_notes',
                'approved_by',
                'approved_at',
                'subscription_renewal_date',
                'renewal_notification_sent'
            ]);
        });
    }
};
