<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The table may have a foreign key on `approved_by`; drop it first to avoid MySQL error 1828.
        try {
            DB::statement('ALTER TABLE `user_deletion_requests` DROP FOREIGN KEY `user_deletion_requests_approved_by_foreign`');
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::table('user_deletion_requests', function (Blueprint $table) {
            $table->dropColumn('approved_by');
            $table->dropColumn('approved_at');

            $table->unsignedBigInteger('manager_approved_by')->nullable()->after('reason');
            $table->timestamp('manager_approved_at')->nullable()->after('manager_approved_by');
            $table->unsignedBigInteger('admin_approved_by')->nullable()->after('manager_approved_at');
            $table->timestamp('admin_approved_at')->nullable()->after('admin_approved_by');
            $table->unsignedBigInteger('super_admin_approved_by')->nullable()->after('admin_approved_at');
            $table->timestamp('super_admin_approved_at')->nullable()->after('super_admin_approved_by');
            $table->string('current_approval_level')->default('manager')->after('rejection_reason');

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
        Schema::table('user_deletion_requests', function (Blueprint $table) {
            $table->dropForeign(['manager_approved_by']);
            $table->dropForeign(['admin_approved_by']);
            $table->dropForeign(['super_admin_approved_by']);

            $table->dropColumn([
                'manager_approved_by',
                'manager_approved_at',
                'admin_approved_by',
                'admin_approved_at',
                'super_admin_approved_by',
                'super_admin_approved_at',
                'current_approval_level'
            ]);

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
        });
    }
};
