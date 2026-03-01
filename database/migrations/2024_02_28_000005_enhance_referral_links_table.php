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
        Schema::table('referral_links', function (Blueprint $table) {
            $table->string('link_type')->default('auto')->after('logo_url'); // manual, auto
            $table->string('custom_code')->nullable()->after('link_type');
            $table->boolean('is_active')->default(true)->after('custom_code');
            $table->integer('click_count')->default(0)->after('is_active');
            $table->integer('conversion_count')->default(0)->after('click_count');
        });

        // Create pivot table for user_referral_links
        Schema::create('user_referral_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('referral_link_id')->constrained()->onDelete('cascade');
            $table->string('custom_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'referral_link_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_links', function (Blueprint $table) {
            $table->dropColumn([
                'link_type',
                'custom_code',
                'is_active',
                'click_count',
                'conversion_count'
            ]);
        });

        Schema::dropIfExists('user_referral_links');
    }
};
