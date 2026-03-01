<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate old statuses to the new 9-stage lifecycle values.
        DB::table('leads')->where('status', 'under_review')->update(['status' => 'new']);
        DB::table('leads')->where('status', 'contacting')->update(['status' => 'call_in_progress']);
        DB::table('leads')->where('status', 'contacted')->update(['status' => 'first_contact']);
        DB::table('leads')->where('status', 'interested')->update(['status' => 'negotiation']);
        DB::table('leads')->where('status', 'proposal_sent')->update(['status' => 'quotation']);
        DB::table('leads')->whereIn('status', ['lost', 'cancelled'])->update(['status' => 'rejected']);

        // Best-effort: update the DB default (avoid hard dependency on doctrine/dbal)
        try {
            DB::statement("ALTER TABLE `leads` ALTER COLUMN `status` SET DEFAULT 'new'");
        } catch (\Throwable $e) {
            // ignore
        }

        // Keep column as string; no schema change needed beyond default.
        if (Schema::hasColumn('leads', 'status')) {
            Schema::table('leads', function (Blueprint $table) {
                // no-op
            });
        }
    }

    public function down(): void
    {
        // Reverse mapping to legacy statuses (best-effort)
        DB::table('leads')->where('status', 'new')->update(['status' => 'under_review']);
        DB::table('leads')->where('status', 'call_in_progress')->update(['status' => 'contacting']);
        DB::table('leads')->where('status', 'first_contact')->update(['status' => 'contacted']);
        DB::table('leads')->where('status', 'quotation')->update(['status' => 'proposal_sent']);
        DB::table('leads')->where('status', 'rejected')->update(['status' => 'cancelled']);

        try {
            DB::statement("ALTER TABLE `leads` ALTER COLUMN `status` SET DEFAULT 'under_review'");
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
