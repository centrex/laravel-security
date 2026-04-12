<?php

declare(strict_types = 1);

namespace Centrex\Security\Events;

final readonly class RiskEvaluated
{
    public function __construct(
        public ?int $userId,
        public string $ip,
        public int $score,
        public array $flags,
        public array $signals,
    ) {}
}
