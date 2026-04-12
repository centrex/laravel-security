<?php

declare(strict_types = 1);

namespace Centrex\Security\Listeners;

use Centrex\Security\Events\RiskFlagRaised;
use Centrex\Security\Models\SecurityRiskFlag;
use Illuminate\Contracts\Queue\ShouldQueue;

final class PersistRiskFlag implements ShouldQueue
{
    public string $queue = 'security';

    public function handle(RiskFlagRaised $event): void
    {
        SecurityRiskFlag::create([
            'user_id'   => $event->userId,
            'flag_type' => $event->flagType,
            'severity'  => $event->severity,
            'reason'    => $event->reason,
            'evidence'  => $event->evidence,
        ]);
    }
}
