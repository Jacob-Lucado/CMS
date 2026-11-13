<?php

namespace App\Models\Shop;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $session_id
 * @property int $amount Minor units, like cents.
 * @property string $currency
 * @property string $status open, paid or expired
 * @property string|null $payment_intent
 * @property Carbon|null $credited_at
 * @property Carbon $created_at
 */
class WebsiteStripePayment extends Model
{
    public const OPEN = 'open';

    public const PAID = 'paid';

    public const EXPIRED = 'expired';

    protected $table = 'website_stripe_payments';

    protected $fillable = ['user_id', 'session_id', 'amount', 'currency', 'status', 'payment_intent', 'credited_at'];

    protected function casts(): array
    {
        return ['credited_at' => 'datetime', 'amount' => 'integer'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
