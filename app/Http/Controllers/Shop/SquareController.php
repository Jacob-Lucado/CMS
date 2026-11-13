<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountTopupFormRequest;
use App\Services\Payments\SquareCheckout;
use App\Support\AuthenticatedUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SquareController extends Controller
{
    public function checkout(AccountTopupFormRequest $request, SquareCheckout $square): RedirectResponse
    {
        abort_unless(SquareCheckout::isConfigured(), 404);
        $user = AuthenticatedUser::from($request);

        try {
            return redirect()->away($square->start($user, $request->integer('amount')));
        } catch (Throwable $exception) {
            Log::warning('Square checkout could not start.', ['user_id' => $user->id, 'error' => $exception->getMessage()]);

            return redirect()->route('shop.index')->withErrors(['message' => __('We could not reach the payment page. Please try again in a moment.')]);
        }
    }

    public function return(Request $request, SquareCheckout $square): RedirectResponse
    {
        $reference = (string) $request->validate(['ref' => ['required', 'uuid']])['ref'];
        $payment = AuthenticatedUser::from($request)->squarePayments()->where('reference', $reference)->first();

        try {
            $paid = $payment?->order_id !== null && $square->settle((string) $payment->order_id);
        } catch (Throwable $exception) {
            Log::warning('Square payment could not be checked on return.', ['error' => $exception->getMessage()]);
            $paid = false;
        }

        if ($paid) {
            return redirect()->route('shop.index')->with('success', __('Thanks! Your balance has been topped up.'));
        }

        return redirect()->route('shop.index')->withErrors(['message' => __('Your payment is still processing. Your balance updates as soon as Square confirms it.')]);
    }
}
