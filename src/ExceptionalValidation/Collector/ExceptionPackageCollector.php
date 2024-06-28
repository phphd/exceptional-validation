<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Collector;

use PhPhD\ExceptionalValidation\Model\Dto\ThrownExceptionPackage;
use Throwable;

interface ExceptionPackageCollector
{
    public function collect(Throwable $exception): ThrownExceptionPackage;
}
