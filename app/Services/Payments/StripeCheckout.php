<?php

namespace App\Services\Payments;

use App\Models\Shop\WebsiteStripePayment;
use App\Models\User;
use App\Support\StorefrontMoney;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Tops up a player's website balance through Stripe Checkout.
 *
 * Money is only credited after asking Stripe for the session, never from what the browser or a webhook says.
 */
class StripeCheckout
{
    private const API = 'https://api.stripe.com/v1';

    public static function isConfigured(): bool
    {
        return str_starts_with((string) config('habbo.stripe.secret'), 'sk_');
    }

    /** Starts a Checkout session and returns the Stripe page to send the player to. */
    public function start(User $user, int $majorAmount): string
    {
        $money = StorefrontMoney::fromMajor($majorAmount);
        $currency = $money->getCurrency()->getCurrencyCode();
        $minor = StorefrontMoney::minorAmount($money);

        $response = $this->client()->asForm()->post(self::API . '/checkout/sessions', [
            'mode' => 'payment',
            'client_reference_id' => (string) $user->id,
            'metadata' => ['user_id' => (string) $user->id],
            'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe.cancel'),
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($currency),
                    'unit_amount' => $minor,
                    'product_data' => ['name' => __(':hotel shop credit', ['hotel' => setting('hotel_name') ?: config('app.name')])],
                ],
            ]],
        ]);

        if ($response->failed() || ! is_string($response->json('id')) || ! is_string($response->json('url'))) {
            throw new RuntimeException('Stripe did not start a checkout session (HTTP ' . $response->status() . ').');
        }

        WebsiteStripePayment::query()->create([
            'user_id' => $user->id,
            'session_id' => $response->json('id'),
            'amount' => $minor,
            'currency' => $currency,
        ]);

        return (string) $response->json('url');
    }

    /** Asks Stripe about a session and credits the player once if it is paid. Returns whether it is paid. */
    public function settle(string $sessionId): bool
    {
        $payment = WebsiteStripePayment::query()->where('session_id', $sessionId)->first();

        if ($payment === null) {
            return false;
        }

        if ($payment->credited_at !== null) {
            return true;
        }

        $session = $this->client()->get(self::API . '/checkout/sessions/' . rawurlencode($sessionId));

        if ($session->failed()) {
            throw new RuntimeException('Stripe could not be asked about a checkout session (HTTP ' . $session->status() . ').');
        }

        if ($session->json('status') === 'expired') {
            $payment->update(['status' => WebsiteStripePayment::EXPIRED]);

            return false;
        }

        $paid = $session->json('payment_status') === 'paid'
            && (int) $session->json('amount_total') === $payment->amount
            && strtoupper((string) $session->json('currency')) === $payment->currency;

        if (! $paid) {
            return false;
        }

        return DB::transaction(function () use ($payment, $session): bool {
            $locked = WebsiteStripePayment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->credited_at === null) {
                User::query()->whereKey($locked->user_id)->lockForUpdate()->firstOrFail()->increment('website_balance', $locked->amount);
                $locked->update([
                    'status' => WebsiteStripePayment::PAID,
                    'payment_intent' => is_string($session->json('payment_intent')) ? $session->json('payment_intent') : null,
                    'credited_at' => now(),
                ]);
            }

            return true;
        });
    }

    /** Checks the Stripe-Signature header the way Stripe documents it: HMAC-SHA256 of "timestamp.payload". */
    public static function signatureIsValid(string $payload, string $header, int $tolerance = 300): bool
    {
        $secret = (string) config('habbo.stripe.webhook_secret');

        if ($secret === '' || $header === '') {
            return false;
        }

        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
            if ($key === 't') {
                $timestamp = (int) $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        if ($timestamp === null || abs(time() - $timestamp) > $tolerance) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }

    private function client(): PendingRequest
    {
        return Http::withToken((string) config('habbo.stripe.secret'))->timeout(15)->acceptJson();
    }
}
