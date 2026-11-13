<?php

use App\Models\Shop\WebsiteStripePayment;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    installHotel();
    setSetting('force_staff_2fa', '0');
    config(['habbo.stripe.secret' => 'sk_test_123', 'habbo.stripe.webhook_secret' => 'whsec_test', 'habbo.paypal.currency' => 'USD']);
});

function stripeSession(string $status = 'complete', string $paymentStatus = 'paid', int $amount = 500): array
{
    return ['id' => 'cs_test_1', 'status' => $status, 'payment_status' => $paymentStatus, 'amount_total' => $amount, 'currency' => 'usd', 'payment_intent' => 'pi_1'];
}

it('sends the player to Stripe Checkout for the amount they picked', function () {
    Http::fake(['api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_test_1', 'url' => 'https://checkout.stripe.com/c/pay/cs_test_1'])]);
    $player = User::factory()->create();

    $this->actingAs($player)->post(route('stripe.checkout'), ['amount' => 5])->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_1');

    Http::assertSent(fn ($request) => $request['line_items'][0]['price_data']['unit_amount'] === 500 && $request['client_reference_id'] === (string) $player->id);
    expect(WebsiteStripePayment::query()->first())->amount->toBe(500)->status->toBe('open');
});

it('credits a paid session exactly once on return', function () {
    Http::fake(['api.stripe.com/v1/checkout/sessions/cs_test_1' => Http::response(stripeSession())]);
    $player = User::factory()->create(['website_balance' => 0]);
    WebsiteStripePayment::query()->create(['user_id' => $player->id, 'session_id' => 'cs_test_1', 'amount' => 500, 'currency' => 'USD']);

    $this->actingAs($player)->get(route('stripe.success', ['session_id' => 'cs_test_1']))->assertRedirect(route('shop.index'))->assertSessionHas('success');
    $this->actingAs($player)->get(route('stripe.success', ['session_id' => 'cs_test_1']));

    expect($player->fresh()->website_balance)->toBe(500)
        ->and(WebsiteStripePayment::query()->first()->status)->toBe('paid');
});

it('does not credit an unpaid or mismatched session, or another player\'s', function () {
    $player = User::factory()->create(['website_balance' => 0]);
    WebsiteStripePayment::query()->create(['user_id' => $player->id, 'session_id' => 'cs_test_1', 'amount' => 500, 'currency' => 'USD']);

    Http::fake(['api.stripe.com/*' => Http::sequence()->push(stripeSession(paymentStatus: 'unpaid'))->push(stripeSession(amount: 100))->push(stripeSession())]);
    $this->actingAs($player)->get(route('stripe.success', ['session_id' => 'cs_test_1']))->assertSessionHasErrors();
    $this->actingAs($player)->get(route('stripe.success', ['session_id' => 'cs_test_1']));
    $this->actingAs(User::factory()->create())->get(route('stripe.success', ['session_id' => 'cs_test_1']));

    expect($player->fresh()->website_balance)->toBe(0);
});

it('credits from a signed webhook and rejects a forged one', function () {
    Http::fake(['api.stripe.com/v1/checkout/sessions/cs_test_1' => Http::response(stripeSession())]);
    $player = User::factory()->create(['website_balance' => 0]);
    WebsiteStripePayment::query()->create(['user_id' => $player->id, 'session_id' => 'cs_test_1', 'amount' => 500, 'currency' => 'USD']);
    $payload = json_encode(['type' => 'checkout.session.completed', 'data' => ['object' => ['id' => 'cs_test_1']]]);
    $time = time();

    $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => "t={$time},v1=forged", 'CONTENT_TYPE' => 'application/json'], $payload)->assertStatus(400);
    expect($player->fresh()->website_balance)->toBe(0);

    $signature = hash_hmac('sha256', $time . '.' . $payload, 'whsec_test');
    $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => "t={$time},v1={$signature}", 'CONTENT_TYPE' => 'application/json'], $payload)->assertOk();

    expect($player->fresh()->website_balance)->toBe(500);
});

it('shows the card top-up only when Stripe is set up', function () {
    $player = User::factory()->create();

    $this->actingAs($player)->get(route('shop.index'))->assertOk()->assertSee('Top up with card')->assertDontSee('PayPal');
    config(['habbo.stripe.secret' => '']);
    $this->actingAs($player)->get(route('shop.index'))->assertOk()->assertSee('Top-ups are not switched on yet.');
});
