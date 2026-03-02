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
        Schema::create('lead_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('reason')->nullable();

            $table->unsignedBigInteger('manager_approved_by')->nullable();
            $table->timestamp('manager_approved_at')->nullable();
            $table->unsignedBigInteger('admin_approved_by')->nullable();
            $table->timestamp('admin_approved_at')->nullable();
            $table->unsignedBigInteger('super_admin_approved_by')->nullable();
            $table->timestamp('super_admin_approved_at')->nullable();

            $table->text('rejection_reason')->nullable();
            $table->string('current_approval_level')->default('manager');
            $table->timestamps();

            $table->foreign('manager_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('admin_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('super_admin_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_deletion_requests');
    }
};
