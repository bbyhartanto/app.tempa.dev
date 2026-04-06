<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default pricing - can be adjusted later via admin panel
        $plans = [
            [
                'tier' => 'basic',
                'billing_cycle' => '3_months',
                'price' => 50000, // Rp 50,000 per 3 months
                'item_limit' => 25,
                'is_active' => true,
            ],
            [
                'tier' => 'basic',
                'billing_cycle' => '1_year',
                'price' => 150000, // Rp 150,000 per year
                'item_limit' => 25,
                'is_active' => true,
            ],
            [
                'tier' => 'standard',
                'billing_cycle' => '3_months',
                'price' => 100000, // Rp 100,000 per 3 months
                'item_limit' => 60,
                'is_active' => true,
            ],
            [
                'tier' => 'standard',
                'billing_cycle' => '1_year',
                'price' => 350000, // Rp 350,000 per year
                'item_limit' => 60,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                [
                    'tier' => $plan['tier'],
                    'billing_cycle' => $plan['billing_cycle'],
                ],
                $plan
            );
        }
    }
}
