<?php

declare(strict_types = 1);

namespace Centrex\Security\Events;

final readonly class UserBlocked
{
    public function __construct(
        public int $userId,
        public string $reason,
    ) {}
}
