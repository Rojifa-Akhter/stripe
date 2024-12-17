<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;

class accountConnectController extends Controller
{
//create connect account
    public function stripeConnectAccount(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // Check if account already exists
            $vendor = Vendor::where('email', $request->email)->first();
            if ($vendor) {
                return response()->json(['error' => 'Account already exists with this email.']);
            }

            // Create a connected account
            $account = \Stripe\Account::create([
                'type' => 'express',
                'email' => $request->email,
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
            ]);

            // Save the connected account ID
            $vendor = new Vendor();
            $vendor->name = $request->name;
            $vendor->email = $request->email;
            $vendor->stripe_account_id = $account->id;
            $vendor->save();

            // Generate an onboarding link
            $accountLink = \Stripe\AccountLink::create([
                'account' => $account->id,
                'refresh_url' => 'https://yourwebsite.com/reauth',
                'return_url' => 'https://yourwebsite.com/dashboard',
                'type' => 'account_onboarding',
            ]);

            return response()->json([
                'account_id' => $account->id,
                'onboarding_url' => $accountLink->url,
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function createPaymentIntent(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $vendor = Vendor::where('email', $request->email)->first();
            if (!$vendor) {
                return response()->json(['error' => 'Connected account not found.']);
            }

            // Fetch the connected account details
            $account = \Stripe\Account::retrieve($vendor->stripe_account_id);
            if (!$account->capabilities['transfers'] || $account->capabilities['transfers'] !== 'active') {
                return response()->json(['error' => 'Connected account is not active for transfers.']);
            }
            // Create a PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount,
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'transfer_data' => [
                    'destination' => $vendor->stripe_account_id,
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function updateConnectedAccount(Request $request)
    {

        $stripe = new StripeClient(env('STRIPE_SECRET'));

        $stripe->accounts->update(
            'acct_1QKdbfE9LCS0mhSu',
            [
                'business_type' => 'company',
                'company' => [
                    'name' => 'Your Business Name',
                    'tax_id' => '123456789',
                ],
                'business_profile' => [
                    'url' => 'https://yourbusinesswebsite.com',
                    'mcc' => '5734',
                    'product_description' => 'Your product description here',
                ],
                'tos_acceptance' => [
                    'date' => time(),
                    'ip' => "103.174.189.193",
                ],
            ]
        );

    }

    public function deleteConnectedAccount(Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));

        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()]);
            }

            $validated = $validator->validated();

            $vendor = Vendor::where('email', $request->email)->first();
            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connected account not found.',
                ]);
            }

            $stripe->accounts->delete($vendor->stripe_account_id);
            $vendor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Connected account deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting connected account: ' . $e->getMessage(),
            ]);
        }
    }

}
