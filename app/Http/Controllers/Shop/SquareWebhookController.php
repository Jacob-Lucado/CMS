<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\Payments\SquareCheckout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Square calls this when a payment changes, so a top-up is credited even if the player closed the tab.
 */
class SquareWebhookController extends Controller
{
    public function __invoke(Request $request, SquareCheckout $square): JsonResponse
    {
        $payload = $request->getContent();

        if (! SquareCheckout::signatureIsValid($payload, (string) $request->header('x-square-hmacsha256-signature'))) {
            return response()->json(['error' => 'Invalid signature.'], 403);
        }

        $event = json_decode($payload, true);
        $payment = is_array($event) ? ($event['data']['object']['payment'] ?? null) : null;

        if (is_array($payment) && ($payment['status'] ?? '') === 'COMPLETED' && is_string($payment['order_id'] ?? null)) {
            try {
                $square->settle($payment['order_id']);
            } catch (Throwable $exception) {
                report($exception);

                return response()->json(['error' => 'Try again later.'], 503);
            }
        }

        return response()->json(['received' => true]);
    }
}
