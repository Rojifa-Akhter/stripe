<?php

namespace App\Http\Controllers;


use Stripe\Price;
use Stripe\Stripe;
use App\Models\Plan;
use App\Models\Order;
use App\Models\User;  
use App\Models\Payment;
use Stripe\StripeClient;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentController extends Controller
{
    // subscribe plan payment
    public function checkout(Request $request, $plan_id)
    {
        // Get the plan from the database
        $plan = Plan::find($plan_id);
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found',
            ], 404); // Not Found
        }

        // Set Stripe API key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Define line items for the checkout session
        $lineItems = [[
            'price' => $plan->stripe_plan_id,
            'quantity' => 1,
        ]];

        // Create a Stripe Checkout session
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'customer_email' => $request->email,  // Use email passed in the request
            'line_items' => $lineItems,
            'mode' => 'subscription',
            'subscription_data' => [
                'trial_from_plan' => true,
            ],
            'success_url' => route('checkout.success', [], true) . "?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => route('checkout.cancel', [], true),
        ]);

        // Create an order record (optional for tracking)
        $order = new Order();
        $order->status = 'unpaid';
        $order->total_price = $plan->price;
        $order->session_id = $session->id;
        $order->user_id = null;  // No user ID since we're avoiding authentication
        $order->save();

        return response()->json([
            'url' => $session->url,
        ]);
    }

    // Success route after payment
    public function success(Request $request)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $sessionId = $request->get('session_id');

        try {
            // Retrieve the session
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            if (!$session) {
                throw new NotFoundHttpException('Session not found.');
            }

            // Find the order associated with this session
            $order = Order::where('session_id', $session->id)->first();
            if (!$order) {
                throw new NotFoundHttpException('Order not found.');
            }

            // Update order status
            if ($order->status === 'unpaid') {
                $order->status = 'paid';
                $order->save();
            }

            // Save payment details
            $payment = new Payment();
            $payment->total = $order->total_price;
            $payment->order_id = $order->id;
            $payment->stripe_cus_id = $session->customer ?? null;
            $payment->stripe_sub_id = $session->subscription ?? null;
            $payment->stripe_payment_intent_id = $session->payment_intent ?? null;
            $payment->stripe_payment_method = $session->payment_method_types[0] ?? null;
            $payment->stripe_payment_status = $session->payment_status ?? 'incomplete';
            $payment->date = $session->created ?? null;
            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'data' => [
                    'order' => $order,
                    'payment' => $payment,
                ],
            ]);
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Payment success error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Cancel route after payment
    public function cancel()
    {
        return response()->json(['message'=>'Cancel Payment']);
    }

    // one way payment
    public function Payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',

        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Create the Payment Intent with manual confirmation
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100, // Amount in cents
                'currency' => 'usd',
                'payment_method' => $request->payment_method,
                'confirmation_method' => 'manual', // Set manual confirmation
                'confirm' => false, // Confirm the payment immediately
            ]);

            // Return success response with payment intent data
            return $this->sendResponse($paymentIntent, 'Payment intent created successfully.');
        } catch (\Exception $e) {
            // Return error response if something goes wrong
            return $this->sendError('Payment failed.', $e->getMessage(), 500);
        }
    }

    /**
     * Send a successful response.
     *
     * @param mixed $result
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($result, $message)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $result,
        ], 200);
    }

    /**
     * Send an error response.
     *
     * @param string $error
     * @param array|string $errorMessages
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        return response()->json([
            'success' => false,
            'message' => $error,
            'errors' => $errorMessages,
        ], $code);
    }
}
