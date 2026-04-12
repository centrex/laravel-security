<?php

declare(strict_types = 1);

namespace Centrex\Security\Actions;

use Centrex\Security\Models\SecurityRiskFlag;

final class ResolveRiskFlag
{
    public function handle(int $flagId): void
    {
        SecurityRiskFlag::findOrFail($flagId)->resolve();
    }
}
