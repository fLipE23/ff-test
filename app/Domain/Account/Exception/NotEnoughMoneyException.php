<?php

namespace App\Domain\Account\Exception;

use Exception;

class NotEnoughMoneyException extends Exception
{
    public function __construct()
    {
        parent::__construct('Not enough money');
    }
}

