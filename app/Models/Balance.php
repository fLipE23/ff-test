<?php

namespace App\Models;

use App\Domain\Account\Value\Amount;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $blocked_amount
 * @property int $amount
 * @property string $currency
 */
class Balance extends Model
{

    protected $fillable = [
        'user_id',
        'amount',
        'blocked_amount',
        'currency',
    ];

    public function getAmount(): Amount
    {
        return new Amount($this->amount, $this->getCurrency());
    }

    public function getBlockedAmount(): Amount
    {
        return new Amount($this->blocked_amount, $this->getCurrency());
    }

    public function getAvailableAmount(): Amount
    {
        return new Amount($this->amount - $this->blocked_amount, $this->getCurrency());
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

}
