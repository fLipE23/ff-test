<?php

namespace App\Domain\Account\DTO;

class OperationHandleResult
{
    public function __construct(
        private bool $success,
        private string $message = '',
    ) {
    }

    public function toArray(): array
    {
        return [
            'success' => $this->isSuccess(),
            'message' => $this->getMessage(),
        ];
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }


}

