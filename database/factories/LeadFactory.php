<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sectors = ['العقارات', 'التقنية والبرمجة', 'التسويق والدعاية', 'التجارة الإلكترونية', 'التعليم', 'الصحة', 'الخدمات المالية', 'المقاولات والبناء', 'المطاعم والكافيهات'];
        $cities = ['الرياض', 'جدة', 'الدمام', 'مكة المكرمة', 'المدينة المنورة', 'الخبر', 'أبها', 'تبوك', 'بريدة'];

        return [
            'user_id' => User::where('role', 'affiliate')->inRandomOrder()->first()?->id ?? User::factory(),
            'client_name' => $this->faker->name(),
            'company_name' => $this->faker->company(),
            'city' => $this->faker->randomElement($cities),
            'client_phone' => '05' . $this->faker->numerify('########'),
            'email' => $this->faker->safeEmail(),
            'status' => $this->faker->randomElement([
                Lead::STATUS_NEW,
                Lead::STATUS_FIRST_CONTACT,
                Lead::STATUS_CALL_IN_PROGRESS,
                Lead::STATUS_APPOINTMENT,
                Lead::STATUS_QUOTATION,
                Lead::STATUS_NEGOTIATION,
                Lead::STATUS_PAUSED,
                Lead::STATUS_SOLD,
                Lead::STATUS_REJECTED,
            ]),
            'commission_type' => $this->faker->randomElement(['percentage', 'fixed']),
            'commission_rate' => $this->faker->numberBetween(5, 15),
            'sector' => $this->faker->randomElement($sectors),
            'recommended_systems' => $this->faker->randomElements(['qoyod', 'daftra'], $this->faker->numberBetween(0, 2)),
            'expected_deal_value' => $this->faker->numberBetween(1000, 50000),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Indicate that the lead is sold.
     */
    public function sold(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Lead::STATUS_SOLD,
            'subscription_renewal_date' => now()->addDays($this->faker->numberBetween(-5, 45)),
        ]);
    }
}
