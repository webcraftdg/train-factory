<?php

namespace App\Service;

final class ErrorResolver
{
    public function __construct(
        public readonly ?object $entity = null,
        public readonly array $errors = [],
    ) {}

    public function isValid(): bool
    {
        return $this->errors === [];
    }
}
