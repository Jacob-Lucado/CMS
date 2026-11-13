<?php

namespace App\Services\Payments;

use App\Models\Shop\WebsiteSquarePayment;
use App\Models\User;
use App\Support\StorefrontMoney;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Tops up a player's website balance through a Square payment link.
 *
 * Square leaves a paid payment-link order OPEN, so an order counts as paid when it has a tender, nothing is
 * left due, and its total matches what we asked for. Money is only credited after asking Square, never from
 * what the browser or a webhook says.
 */
class SquareCheckout
{
    private const VERSION = '2024-10-17';

    public static function isConfigured(): bool
    {
        return filled(config('habbo.square.access_token')) && filled(config('habbo.square.location_id'));
    }

    /** Creates a payment link and returns the Square page to send the player to. */
    public function start(User $user, int $majorAmount): string
    {
        $money = StorefrontMoney::fromMajor($majorAmount);
        $currency = $money->getCurrency()->getCurrencyCode();
        $minor = StorefrontMoney::minorAmount($money);
        $reference = (string) Str::uuid();

        $response = $this->client()->post('/v2/online-checkout/payment-links', [
            'idempotency_key' => $reference,
            'quick_pay' => [
                'name' => __(':hotel shop credit', ['hotel' => setting('hotel_name') ?: config('app.name')]),
                'price_money' => ['amount' => $minor, 'currency' => $currency],
                'location_id' => (string) config('habbo.square.location_id'),
            ],
            'checkout_options' => ['redirect_url' => route('square.return', ['ref' => $reference])],
            'payment_note' => 'Top-up for ' . $user->username . ' (#' . $user->id . ')',
        ]);

        $url = $response->json('payment_link.url');
        $orderId = $response->json('payment_link.order_id');

        if ($response->failed() || ! is_string($url) || ! is_string($orderId)) {
            throw new RuntimeException('Square did not create a payment link (HTTP ' . $response->status() . ').');
        }

        WebsiteSquarePayment::query()->create([
            'user_id' => $user->id,
            'reference' => $reference,
            'order_id' => $orderId,
            'payment_link_id' => is_string($response->json('payment_link.id')) ? $response->json('payment_link.id') : null,
            'amount' => $minor,
            'currency' => $currency,
        ]);

        return $url;
    }

    /** Asks Square about an order and credits the player once if it is paid. Returns whether it is paid. */
    public function settle(string $orderId): bool
    {
        $payment = WebsiteSquarePayment::query()->where('order_id', $orderId)->first();

        if ($payment === null) {
            return false;
        }

        if ($payment->credited_at !== null) {
            return true;
        }

        $response = $this->client()->get('/v2/orders/' . rawurlencode($orderId));

        if ($response->failed()) {
            throw new RuntimeException('Square could not be asked about an order (HTTP ' . $response->status() . ').');
        }

        $order = (array) $response->json('order');
        $tenders = (array) ($order['tenders'] ?? []);
        $paid = $tenders !== []
            && (int) ($order['net_amount_due_money']['amount'] ?? -1) === 0
            && (int) ($order['total_money']['amount'] ?? -1) === $payment->amount
            && strtoupper((string) ($order['total_money']['currency'] ?? '')) === $payment->currency;

        if (! $paid) {
            return false;
        }

        $paymentId = $tenders[0]['payment_id'] ?? ($tenders[0]['id'] ?? null);

        return DB::transaction(function () use ($payment, $paymentId): bool {
            $locked = WebsiteSquarePayment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->credited_at === null) {
                User::query()->whereKey($locked->user_id)->lockForUpdate()->firstOrFail()->increment('website_balance', $locked->amount);
                $locked->update(['status' => WebsiteSquarePayment::PAID, 'payment_id' => is_string($paymentId) ? $paymentId : null, 'credited_at' => now()]);
            }

            return true;
        });
    }

    /**
     * Square signs a webhook with base64(HMAC-SHA256(notification URL + body)) using the subscription's signature key.
     */
    public static function signatureIsValid(string $payload, string $signature): bool
    {
        $key = (string) config('habbo.square.webhook_signature_key');

        if ($key === '' || $signature === '') {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', self::webhookUrl() . $payload, $key, true));

        return hash_equals($expected, $signature);
    }

    /** The exact URL saved on the Square webhook subscription; the signature covers it. */
    public static function webhookUrl(): string
    {
        return (string) (config('habbo.square.webhook_url') ?: route('square.webhook'));
    }

    private function client(): PendingRequest
    {
        $base = config('habbo.square.environment') === 'production' ? 'https://connect.squareup.com' : 'https://connect.squareupsandbox.com';

        return Http::baseUrl($base)
            ->withToken((string) config('habbo.square.access_token'))
            ->withHeaders(['Square-Version' => self::VERSION])
            ->timeout(15)
            ->acceptJson()
            ->asJson();
    }
}
