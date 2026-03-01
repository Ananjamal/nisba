<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, number, boolean, json
            $table->string('group')->default('general'); // general, tax, withdrawal, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('system_settings')->insert([
            ['key' => 'tax_rate', 'value' => '15', 'type' => 'number', 'group' => 'tax', 'description' => 'نسبة الضريبة (%)'],
            ['key' => 'min_withdrawal_amount', 'value' => '100', 'type' => 'number', 'group' => 'withdrawal', 'description' => 'الحد الأدنى للسحب'],
            ['key' => 'max_withdrawal_amount', 'value' => '10000', 'type' => 'number', 'group' => 'withdrawal', 'description' => 'الحد الأقصى للسحب'],
            ['key' => 'renewal_notification_days', 'value' => '30', 'type' => 'number', 'group' => 'subscription', 'description' => 'عدد الأيام قبل إرسال تنبيه التجديد'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
