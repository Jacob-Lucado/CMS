<?php

namespace App\Models\Shop;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $reference Our id for the checkout, carried in the return link.
 * @property string|null $order_id
 * @property string|null $payment_link_id
 * @property int $amount Minor units, like cents.
 * @property string $currency
 * @property string $status open or paid
 * @property string|null $payment_id
 * @property Carbon|null $credited_at
 */
class WebsiteSquarePayment extends Model
{
    public const OPEN = 'open';

    public const PAID = 'paid';

    protected $table = 'website_square_payments';

    protected $fillable = ['user_id', 'reference', 'order_id', 'payment_link_id', 'amount', 'currency', 'status', 'payment_id', 'credited_at'];

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
