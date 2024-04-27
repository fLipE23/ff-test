<?php

namespace App\Domain\Account\Value;


class Amount
{

    public function __construct(
        private int $amount,
        private string $currency
    ) {
        if (!$this->validateAmount($this->amount)) {
            throw new \InvalidArgumentException('Amount should not be negative');
        }

        if (!$this->validateCurrency($this->currency)) {
            throw new \InvalidArgumentException('Currency is not valid');
        }
    }

    public function getValue(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    private function validateAmount(int $amount): bool
    {
        return $amount >= 0;
    }

    private function validateCurrency(string $currency): bool
    {
        $currencies = config('currency');
        return array_key_exists($currency, $currencies);
    }

}



