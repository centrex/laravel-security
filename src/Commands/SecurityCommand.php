<?php

declare(strict_types = 1);

namespace Centrex\Security\Commands;

use Illuminate\Console\Command;

class SecurityCommand extends Command
{
    public $signature = 'laravel-security';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
