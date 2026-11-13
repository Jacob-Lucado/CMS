<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\Payments\StripeCheckout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Stripe calls this when a checkout finishes, so a payment is credited even if the player closed the tab.
 */
class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeCheckout $stripe): JsonResponse
    {
        $payload = $request->getContent();

        if (! StripeCheckout::signatureIsValid($payload, (string) $request->header('Stripe-Signature'))) {
            return response()->json(['error' => 'Invalid signature.'], 400);
        }

        $event = json_decode($payload, true);
        $type = is_array($event) ? ($event['type'] ?? '') : '';
        $sessionId = is_array($event) ? ($event['data']['object']['id'] ?? null) : null;

        if (in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded', 'checkout.session.expired'], true) && is_string($sessionId)) {
            try {
                $stripe->settle($sessionId);
            } catch (Throwable $exception) {
                report($exception);

                return response()->json(['error' => 'Try again later.'], 503);
            }
        }

        return response()->json(['received' => true]);
    }
}
