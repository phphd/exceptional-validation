<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Handler;

use PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException;
use PhPhD\ExceptionalValidation\Model\Exception\Adapter\ThrownException;

/** @api */
interface ExceptionHandler
{
    /** @throws ExceptionalValidationFailedException */
    public function capture(object $message, ThrownException $exception): void;
}
