<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_renewals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();

            $table->date('renewal_date');
            $table->date('previous_expiry_date')->nullable();
            $table->date('new_expiry_date')->nullable();

            $table->decimal('renewal_amount', 10, 2)->nullable();
            $table->string('renewal_type')->default('manual'); // manual, automatic

            $table->foreignId('renewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed, cancelled

            $table->string('payment_method')->nullable();
            $table->text('invoice_url')->nullable();

            $table->timestamp('notification_sent_at')->nullable();
            $table->date('grace_period_ends')->nullable();

            $table->timestamps();

            $table->index(['status', 'renewal_date']);
            $table->index('lead_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_renewals');
    }
};
