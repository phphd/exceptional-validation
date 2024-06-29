<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

use PhPhD\ExceptionalValidation\Model\Exception\CaughtException;
use Symfony\Component\Validator\ConstraintViolationInterface;

interface ExceptionViolationFormatter
{
    public function formatViolation(CaughtException $caughtException): ConstraintViolationInterface;
}
