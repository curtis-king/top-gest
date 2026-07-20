<?php

namespace App\Api;

use Exception;

class SfecCertificationException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $status,
        public readonly array $responseBody = []
    ) {
        parent::__construct($message);
    }

    public function isAlreadyCertified(): bool
    {
        return $this->status === 409;
    }
}
