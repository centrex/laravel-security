<?php

declare(strict_types = 1);

namespace Centrex\Security\Concerns;

trait AddTablePrefix
{
    public function getTable(): string
    {
        $prefix = config('security.table_prefix') ?: 'sec_';

        return $prefix . $this->getTableSuffix();
    }

    abstract protected function getTableSuffix(): string;
}
