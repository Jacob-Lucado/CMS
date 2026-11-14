<?php

use App\Models\Shop\WebsiteSquarePayment;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    installHotel();
    setSetting('force_staff_2fa', '0');
    config([
        'habbo.square.access_token' => 'EAAA-test', 'habbo.square.location_id' => 'LOC1', 'habbo.square.environment' => 'sandbox',
        'habbo.square.webhook_signature_key' => 'sig-key', 'habbo.square.webhook_url' => 'https://hotel.test/api/square/webhook',
        'habbo.paypal.currency' => 'USD',
    ]);
});

function squareOrder(int $due = 0, int $total = 500, bool $tendered = true): array
{
    return ['order' => [
        'id' => 'ORDER1', 'state' => 'OPEN',
        'total_money' => ['amount' => $total, 'currency' => 'USD'],
        'net_amount_due_money' => ['amount' => $due, 'currency' => 'USD'],
        'tenders' => $tendered ? [['id' => 'PAY1', 'payment_id' => 'PAY1']] : [],
    ]];
}

function squarePayment(User $player): WebsiteSquarePayment
{
    return WebsiteSquarePayment::query()->create(['user_id' => $player->id, 'reference' => '6f1c6a8e-0c9b-4b0e-9d0a-1f2e3d4c5b6a', 'order_id' => 'ORDER1', 'amount' => 500, 'currency' => 'USD']);
}

it('sends the player to a Square payment link for the amount they picked', function () {
    Http::fake(['connect.squareupsandbox.com/v2/online-checkout/payment-links' => Http::response(['payment_link' => ['id' => 'LINK1', 'url' => 'https://square.link/u/abc', 'order_id' => 'ORDER1']])]);
    $player = User::factory()->create();

    $this->actingAs($player)->post(route('square.checkout'), ['amount' => 5])->assertRedirect('https://square.link/u/abc');

    Http::assertSent(fn ($request) => $request['quick_pay']['price_money'] === ['amount' => 500, 'currency' => 'USD']
        && $request['quick_pay']['location_id'] === 'LOC1'
        && $request->hasHeader('Square-Version')
        && str_contains($request['checkout_options']['redirect_url'], '/square/return?ref='));
    expect(WebsiteSquarePayment::query()->first())->amount->toBe(500)->order_id->toBe('ORDER1')->status->toBe('open');
});

it('credits a paid order exactly once on return', function () {
    Http::fake(['connect.squareupsandbox.com/v2/orders/ORDER1' => Http::response(squareOrder())]);
    $player = User::factory()->create(['website_balance' => 0]);
    $payment = squarePayment($player);

    $this->actingAs($player)->get(route('square.return', ['ref' => $payment->reference]))->assertRedirect(route('shop.index'))->assertSessionHas('success');
    $this->actingAs($player)->get(route('square.return', ['ref' => $payment->reference]));

    expect($player->fresh()->website_balance)->toBe(500)
        ->and($payment->fresh())->status->toBe('paid')->payment_id->toBe('PAY1');
});

it('does not credit an unpaid or mismatched order, or another player\'s', function () {
    $player = User::factory()->create(['website_balance' => 0]);
    $payment = squarePayment($player);

    Http::fake(['connect.squareupsandbox.com/*' => Http::sequence()->push(squareOrder(due: 500, tendered: false))->push(squareOrder(total: 100))->push(squareOrder())]);
    $this->actingAs($player)->get(route('square.return', ['ref' => $payment->reference]))->assertSessionHasErrors();
    $this->actingAs($player)->get(route('square.return', ['ref' => $payment->reference]));
    $this->actingAs(User::factory()->create())->get(route('square.return', ['ref' => $payment->reference]));

    expect($player->fresh()->website_balance)->toBe(0);
});

it('credits from a signed webhook and rejects a forged one', function () {
    Http::fake(['connect.squareupsandbox.com/v2/orders/ORDER1' => Http::response(squareOrder())]);
    $player = User::factory()->create(['website_balance' => 0]);
    squarePayment($player);
    $payload = json_encode(['type' => 'payment.updated', 'data' => ['object' => ['payment' => ['id' => 'PAY1', 'order_id' => 'ORDER1', 'status' => 'COMPLETED']]]]);

    $this->call('POST', route('square.webhook'), [], [], [], ['HTTP_X_SQUARE_HMACSHA256_SIGNATURE' => 'forged', 'CONTENT_TYPE' => 'application/json'], $payload)->assertForbidden();
    expect($player->fresh()->website_balance)->toBe(0);

    $signature = base64_encode(hash_hmac('sha256', 'https://hotel.test/api/square/webhook' . $payload, 'sig-key', true));
    $this->call('POST', route('square.webhook'), [], [], [], ['HTTP_X_SQUARE_HMACSHA256_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'], $payload)->assertOk();

    expect($player->fresh()->website_balance)->toBe(500);
});

it('shows the card top-up only when Square is set up', function () {
    $player = User::factory()->create();

    $this->actingAs($player)->get(route('shop.index'))->assertOk()->assertSee('Top up with card')->assertSee('Square')->assertDontSee('PayPal');
    config(['habbo.square.access_token' => '']);
    $this->actingAs($player)->get(route('shop.index'))->assertOk()->assertSee('Top-ups are not switched on yet.');
});

it('falls back to the other Square environment when the token is turned away', function () {
    Http::fake([
        'connect.squareupsandbox.com/*' => Http::response(['errors' => [['code' => 'UNAUTHORIZED', 'detail' => 'This request could not be authorized.']]], 401),
        'connect.squareup.com/*' => Http::response(['payment_link' => ['id' => 'LINK1', 'url' => 'https://square.link/u/live', 'order_id' => 'ORDER1']]),
    ]);

    $this->actingAs(User::factory()->create())->post(route('square.checkout'), ['amount' => 5])->assertRedirect('https://square.link/u/live');
});

it('tells staff why Square refused, but not players', function () {
    Http::fake(['*' => Http::response(['errors' => [['code' => 'INVALID_VALUE', 'detail' => 'Location not found.']]], 400)]);

    $this->actingAs(User::factory()->create(['rank' => 7]))->post(route('square.checkout'), ['amount' => 5])
        ->assertSessionHasErrors(['message' => 'We could not reach the payment page. Please try again in a moment. (Square did not create a payment link: HTTP 400 INVALID_VALUE Location not found.)']);
    $this->flushSession()->actingAs(User::factory()->create(['rank' => 1]))->post(route('square.checkout'), ['amount' => 5])
        ->assertSessionHasErrors(['message' => 'We could not reach the payment page. Please try again in a moment.']);
});
