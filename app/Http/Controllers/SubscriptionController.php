<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Stripe\Customer;
use Stripe\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{

    public function getAllPlans()
    {
        return Plan::all();
    }

    public function getUserSubscription()
    {
        try {
            $subscription = Subscription::where('user_id', Auth::id())
                ->with('plan')
                ->first();

            if (!$subscription) {
                return response()->json(['error' => 'No active subscription found'], 404);
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $user = Auth::user();

            $stripeSubscription = StripeSubscription::retrieve($subscription->stripe_subscription_id);


            $upcomingInvoice = null;
            $plan = $subscription->plan;
            $nextBillingDate = $subscription->next_billing_date;

            try {
                $upcomingInvoice = Invoice::upcoming([
                    'customer' => $user->stripe_id,
                    'subscription' => $subscription->stripe_subscription_id,
                ]);

                $lastLineItem = end($upcomingInvoice->lines->data);
                $stripePriceId = $lastLineItem->price->id ?? null;

                if ($stripePriceId) {
                    $plan = Plan::where('stripe_price_id', $stripePriceId)->first();
                }

                $nextBillingDate = Carbon::createFromTimestamp($upcomingInvoice->period_end);
            } catch (\Exception $e) {
                Log::info("No upcoming invoice found for subscription ID: {$subscription->stripe_subscription_id}");
            }

            $subscription->status = $stripeSubscription->status;
            $subscription->stripe_subscription_id = $stripeSubscription->id;
            $subscription->next_billing_date = $nextBillingDate;
            $subscription->plan_id = $plan->id ?? $subscription->plan_id;
            $subscription->save();

            return response()->json([
                'subscription' => $subscription,
                'stripeInvoice' => $upcomingInvoice,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function updateSubscription(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = Auth::user();
        $newPlan = Plan::findOrFail($request->plan_id);

        $subscription = Subscription::where('user_id', $user->id)->first();
        if (!$subscription) {
            return response()->json(['error' => 'No subscription found'], 404);
        }

        if (!$subscription->stripe_subscription_id) {
            return response()->json(['error' => 'Invalid Stripe subscription ID'], 422);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            DB::beginTransaction();

            $stripeSubscription = StripeSubscription::retrieve($subscription->stripe_subscription_id);

            $updateParams = [
                'items' => [
                    [
                        'id' => $stripeSubscription->items->data[0]->id,
                        'price' => $newPlan->stripe_price_id,
                    ],
                ],
                'proration_behavior' => 'create_prorations',
            ];


            StripeSubscription::update(
                $stripeSubscription->id,
                $updateParams
            );

            $subscription->plan_id = $newPlan->id;
            $subscription->next_billing_date = $newPlan->billing_interval === 'month'
                ? Carbon::now()->addMonth()
                : Carbon::now()->addYear();
            $subscription->save();

            DB::commit();

            return response()->json([
                'message' => 'Subscription updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }


    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|numeric',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $user = Auth::user();

            $plan = Plan::findOrFail($request->plan_id);

            $existingSubscription = Subscription::where('user_id', $user->id)->first();

            if ($existingSubscription) {
                return $this->updateSubscription(new Request(['plan_id' => $plan->id]));
            }

            DB::beginTransaction();

            $stripeCustomer = $user->createOrGetStripeCustomer();

            $stripeSubscription = StripeSubscription::create([
                'customer' => $stripeCustomer->id,
                'items' => [['price' => $plan->stripe_price_id]],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'plan_id' => $plan->id,
                    'stripe_subscription_id' => $stripeSubscription->id,
                    'start_date' => now(),
                    'status' => $stripeSubscription->status,
                    'next_billing_date' => $plan->billing_interval === 'month'
                        ? now()->addMonth()
                        : now()->addYear(),
                ]
            );

            DB::commit();

            return response()->json(['message' => 'Subscription successful']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating subscription: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function cancelSubscription()
    {

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            DB::beginTransaction();
            $user = Auth::user();

            $subscription = Subscription::where('user_id', $user->id)->first();

            $canceledSubscription = StripeSubscription::update($subscription->stripe_subscription_id, [
                'cancel_at_period_end' => true,
            ]);

            if ($subscription) {
                // $subscription->status = 'canceled';
                $subscription->cancellation_date = now();
                $subscription->save();
            }
            DB::commit();
            return response()->json([
                'message' => 'Subscription canceled successfully.',
                'subscription' => $canceledSubscription,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Stripe subscription cancellation failed: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to cancel the subscription.',
                'details' => $e->getMessage(),
            ], 422);
        }
    }
}
