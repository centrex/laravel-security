<?php

declare(strict_types = 1);

namespace Centrex\Security\Listeners;

use Centrex\Security\Events\RiskFlagRaised;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class NotifySecurityTeam implements ShouldQueue
{
    public function handle(RiskFlagRaised $event): void
    {
        Log::warning('Security risk detected', [
            'user_id'   => $event->userId,
            'flag_type' => $event->flagType,
            'severity'  => $event->severity,
            'reason'    => $event->reason,
            'evidence'  => $event->evidence,
        ]);
    }
}
