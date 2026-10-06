<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Support\Razorpay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class SubscriptionController extends Controller
{
    public function cancel(Request $request, Subscription $subscription, Razorpay $razorpay): RedirectResponse
    {
        abort_unless($subscription->user_id === $request->user()->id && $subscription->canManage(), 404);

        try {
            if ($subscription->razorpay_subscription_id !== null) {
                $razorpay->cancelSubscription($subscription->razorpay_subscription_id);
            }
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['subscription' => 'This subscription could not be cancelled.']);
        }

        $subscription->update(['status' => 'cancelled']);

        return back()->with('status', 'Subscription cancelled.');
    }
}
