<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'                => 'Starter',
                'slug'                => 'starter',
                'description'         => 'Perfect for small parking lots',
                'price_monthly'       => 4900,    // $49/month
                'price_yearly'        => 49000,   // $490/year
                'currency'            => 'USD',
                'max_slots'           => 50,
                'max_staff_users'     => 5,
                'max_tickets_per_day' => 200,
                'features'            => [
                    'sms_notifications' => false,
                    'custom_branding'   => false,
                    'advanced_reports'  => false,
                    'api_access'        => false,
                    'multi_floor'       => false,
                    'valet_tracking'    => false,
                    'priority_support'  => false,
                ],
                'sort_order' => 1,
            ],
            [
                'name'                => 'Professional',
                'slug'                => 'professional',
                'description'         => 'For growing parking operations',
                'price_monthly'       => 9900,    // $99/month
                'price_yearly'        => 99000,   // $990/year
                'currency'            => 'USD',
                'max_slots'           => 500,
                'max_staff_users'     => 25,
                'max_tickets_per_day' => 1000,
                'features'            => [
                    'sms_notifications' => true,
                    'custom_branding'   => true,
                    'advanced_reports'  => true,
                    'api_access'        => false,
                    'multi_floor'       => false,
                    'valet_tracking'    => false,
                    'priority_support'  => false,
                ],
                'sort_order' => 2,
            ],
            [
                'name'                => 'Enterprise',
                'slug'                => 'enterprise',
                'description'         => 'Unlimited everything for large operations',
                'price_monthly'       => 24900,   // $249/month
                'price_yearly'        => 249000,  // $2,490/year
                'currency'            => 'USD',
                'max_slots'           => 99999,
                'max_staff_users'     => 99999,
                'max_tickets_per_day' => 99999,
                'features'            => [
                    'sms_notifications' => true,
                    'custom_branding'   => true,
                    'advanced_reports'  => true,
                    'api_access'        => true,
                    'multi_floor'       => true,
                    'valet_tracking'    => true,
                    'priority_support'  => true,
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
