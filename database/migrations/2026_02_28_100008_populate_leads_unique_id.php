<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Lead;

return new class extends Migration
{
    public function up(): void
    {
        // First, populate unique_id for existing leads
        Lead::whereNull('unique_id')->get()->each(function ($lead) {
            $lead->update(['unique_id' => Lead::generateUniqueId()]);
        });

        // Now make the column NOT NULL
        Schema::table('leads', function (Blueprint $table) {
            $table->string('unique_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('unique_id')->nullable()->change();
        });
    }
};
