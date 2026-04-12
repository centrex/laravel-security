<?php

declare(strict_types=1);

namespace Centrex\Security\Support\Security;

interface RiskEngineInterface
{
    public function evaluate(array $signals): RiskResult;
}
