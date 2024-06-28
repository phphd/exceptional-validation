<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Rule;

use PhPhD\ExceptionalValidation\Model\Dto\ThrownExceptionPackage;
use PhPhD\ExceptionalValidation\Model\ValueObject\CaughtException;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;

interface CaptureRule
{
    /** @return list<CaughtException> */
    public function capture(ThrownExceptionPackage $exceptions): array;

    public function getPropertyPath(): PropertyPath;

    public function getEnclosingObject(): object;

    public function getRoot(): object;

    public function getValue(): mixed;
}
