<?php

declare(strict_types=1);

namespace Centrex\Security\Actions;

use Centrex\Security\Events\UserBlocked;

final class BlockUser
{
    public function handle(int $userId, string $reason): void
    {
        event(new UserBlocked($userId, $reason));
    }
}
