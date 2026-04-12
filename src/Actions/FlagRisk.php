<?php

declare(strict_types = 1);

namespace Centrex\Security\Actions;

use Centrex\Security\Events\RiskFlagRaised;

final class FlagRisk
{
    public function handle(?int $userId, array $signals): void
    {
        event(new RiskFlagRaised($userId, $signals));
    }
}
