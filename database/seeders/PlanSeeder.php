<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use Stripe\Stripe;
use Stripe\Price;

class PlanSeeder extends Seeder
{
    public function run()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $plans = [
            [
                'name' => 'Basic Plan',
                'description' => 'Monthly subscription for basic users.',
                'price' => 9.99,
                'billing_interval' => 'month',
            ],
            [
                'name' => 'Pro Plan',
                'description' => 'Monthly subscription for professional users.',
                'price' => 19.99,
                'billing_interval' => 'month',
            ],
            [
                'name' => 'Premium Plan',
                'description' => 'Yearly subscription for premium users.',
                'price' => 99.99,
                'billing_interval' => 'year',
            ],
        ];

        foreach ($plans as $planData) {
            $stripePrice = Price::create([
                'unit_amount' => $planData['price'] * 100,
                'currency' => 'usd',
                'recurring' => ['interval' => $planData['billing_interval']],
                'product_data' => [
                    'name' => $planData['name'],
                ],
            ]);

            Plan::create(array_merge($planData, ['stripe_price_id' => $stripePrice->id]));
        }
    }
}
