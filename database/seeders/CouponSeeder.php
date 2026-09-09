<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coupon::firstOrCreate(
            ['code' => 'WELCOME50'],
            [
                'name' => 'New Gym Launch Offer 50% Off',
                'description' => 'Get 50% off on your first month or annual SaaS subscription.',
                'discount_type' => 'percentage',
                'discount_value' => 50.00,
                'min_amount' => 500.00,
                'max_discount_amount' => 2500.00,
                'usage_limit' => 200,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'GYM1000'],
            [
                'name' => 'Flat ₹1,000 Off Enterprise Tier',
                'description' => 'Direct ₹1,000 discount on Pro and Enterprise tiers.',
                'discount_type' => 'fixed',
                'discount_value' => 1000.00,
                'min_amount' => 2000.00,
                'usage_limit' => 100,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ]
        );
    }
}
