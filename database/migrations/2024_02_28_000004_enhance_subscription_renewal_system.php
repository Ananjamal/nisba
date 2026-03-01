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
            $table->date('subscription_start_date')->nullable()->after('subscription_renewal_date');
            $table->integer('subscription_duration_months')->default(12)->after('subscription_start_date');
            $table->decimal('subscription_amount', 10, 2)->nullable()->after('subscription_duration_months');
            $table->string('subscription_status')->default('active')->after('subscription_amount'); // active, expired, cancelled
            $table->text('renewal_notes')->nullable()->after('subscription_status');
            $table->boolean('auto_renewal')->default(false)->after('renewal_notes');
            $table->date('last_renewal_notification_sent')->nullable()->after('auto_renewal');
            $table->integer('renewal_count')->default(0)->after('last_renewal_notification_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_start_date',
                'subscription_duration_months',
                'subscription_amount',
                'subscription_status',
                'renewal_notes',
                'auto_renewal',
                'last_renewal_notification_sent',
                'renewal_count'
            ]);
        });
    }
};
