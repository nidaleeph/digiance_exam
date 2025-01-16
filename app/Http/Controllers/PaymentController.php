<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Payment;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Stripe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\Invoice;
use App\Models\Subscription;


class PaymentController extends Controller
{

    public function createPaymentDetails(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $stripeCustomer = $user->createOrGetStripeCustomer();

            $paymentMethod = PaymentMethod::retrieve($request->payment_method_id);
            $paymentMethod->attach(params: ['customer' => $stripeCustomer->id]);

            Customer::update($stripeCustomer->id, [
                'invoice_settings' => ['default_payment_method' => $request->payment_method_id],
            ]);

            DB::commit();

            return response()->json(['message' => 'Success add customer default payment method']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating PaymentIntent: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }


    public function paymentHistory()
    {
        $user = Auth::user();
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntents = PaymentIntent::all([
            'customer' => $user->stripe_id,
        ]);

        try {

            foreach ($paymentIntents->data as $paymentIntent) {


                $invoice = Invoice::retrieve($paymentIntent->invoice);

                $lastLineItem = end($invoice->lines->data);

                $stripePriceId = $lastLineItem->price->id ?? null;

                $plan = Plan::where('stripe_price_id', $stripePriceId)->first();

                if (!$plan) {
                    continue;
                }

                Payment::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'stripe_payment_id' => $paymentIntent->id,
                    ],
                    [
                        'plan_id' => $plan->id,
                        'currency' => $paymentIntent->currency,
                        'status' => $paymentIntent->status,
                        'amount' => $paymentIntent->amount / 100,
                        'payment_date' => \Carbon\Carbon::createFromTimestamp($paymentIntent->created),
                        'updated_at' => now(),
                    ]
                );
            }

            DB::commit();

            $payments = Payment::where('user_id', Auth::id())
                ->with('plan')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json($payments);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error fetching payment history: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
