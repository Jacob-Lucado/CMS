<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountTopupFormRequest;
use App\Services\Payments\StripeCheckout;
use App\Support\AuthenticatedUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class StripeController extends Controller
{
    public function checkout(AccountTopupFormRequest $request, StripeCheckout $stripe): RedirectResponse
    {
        abort_unless(StripeCheckout::isConfigured(), 404);
        $user = AuthenticatedUser::from($request);

        try {
            return redirect()->away($stripe->start($user, $request->integer('amount')));
        } catch (Throwable $exception) {
            Log::warning('Stripe checkout could not start.', ['user_id' => $user->id, 'error' => $exception->getMessage()]);

            return redirect()->route('shop.index')->withErrors(['message' => __('We could not reach the payment page. Please try again in a moment.')]);
        }
    }

    public function success(Request $request, StripeCheckout $stripe): RedirectResponse
    {
        $sessionId = (string) $request->validate(['session_id' => ['required', 'string', 'max:255']])['session_id'];
        $owned = AuthenticatedUser::from($request)->stripePayments()->where('session_id', $sessionId)->exists();

        try {
            $paid = $owned && $stripe->settle($sessionId);
        } catch (Throwable $exception) {
            Log::warning('Stripe payment could not be checked on return.', ['error' => $exception->getMessage()]);
            $paid = false;
        }

        if ($paid) {
            return redirect()->route('shop.index')->with('success', __('Thanks! Your balance has been topped up.'));
        }

        return redirect()->route('shop.index')->withErrors(['message' => __('Your payment is still processing. Your balance updates as soon as Stripe confirms it.')]);
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('shop.index')->withErrors(['message' => __('Payment cancelled. You were not charged.')]);
    }
}
