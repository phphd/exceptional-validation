<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

use PhPhD\ExceptionalValidation\Model\Exception\CaughtException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ExceptionViolationListFormatter
{
    /** @param non-empty-list<CaughtException> $caughtExceptions */
    public function formatViolations(array $caughtExceptions): ConstraintViolationListInterface;
}
